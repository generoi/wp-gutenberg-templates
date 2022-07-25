<?php
// phpcs:ignoreFile PSR1.Classes.ClassDeclaration.MissingNamespace

use Brick\VarExporter\VarExporter;

class GutenbergTemplates_DebugBar extends Debug_Bar_Panel
{
    protected ?WP_Post $post;

    public function init(): void
    {
        $this->post = $this->postObject();
        $this->title('Block Template');
    }

    public function prerender(): void
    {
        $this->set_visible($this->post ? true : false);
    }

    public function render(): void
    {
        $output = VarExporter::export($this->blocks(), VarExporter::INLINE_NUMERIC_SCALAR_ARRAY);
        $output = apply_filters('wp-gutenberg-templates/debugbar/export', $output);
        $output = str_replace(['<', '>'], ['&lt;', '&gt;'], $output);
        echo "<pre>$output</pre>";
    }

    protected function postObject(): ?WP_Post
    {
        $post_id = null;

        if (is_singular()) {
            $post_id = get_the_ID();
        } elseif (is_archive()) {
            if (is_home()) {
                $post_id = get_option('page_for_posts');
            }

            if (!$post_id && function_exists('get_page_for_post_type')) {
                /* @see humanmade/page-for-post-type */
                $post_id = get_page_for_post_type();
            }
        }

        return $post_id ? get_post($post_id) : null;
    }

    /**
     * @return array<mixed>
     */
    protected function blocks(): array
    {
        if (!$this->post || !$this->post->post_content) {
            return [];
        }

        $blocks = parse_blocks($this->post->post_content);
        $blocks = array_map([$this, 'buildBlock'], $blocks);
        $blocks = array_filter($blocks);
        $blocks = array_values($blocks);
        $blocks = apply_filters('wp-gutenberg-templates/debugbar/blocks', $blocks, $this->post);
        return $blocks;
    }

    /**
     * @param array<string,mixed> $block
     * @return ?array<mixed>
     */
    protected function buildBlock($block)
    {
        if (!$block['blockName']) {
            return null;
        }

        $name = $block['blockName'];
        $attrs = $this->buildAttrs($block);

        $innerBlocks = array_map([$this, 'buildBlock'], $block['innerBlocks']);
        $innerBlocks = array_filter($innerBlocks);
        $innerBlocks = array_values($innerBlocks);

        $value = [];
        $value[] = $name;

        if (!empty($attrs)) {
            $value[] = $attrs;
        }

        if (!empty($innerBlocks)) {
            if (empty($attrs)) {
                $value[] = [];
            }
            $value[] = $innerBlocks;
        }
        return $value;
    }

    /**
     * @param array<string,mixed> $block
     * @return array<string,mixed>
     */
    protected function buildAttrs(array $block): array
    {
        $attrs = $block['attrs'];

        if (isset($attrs['align']) && empty($attrs['align'])) {
            unset($attrs['align']);
        }

        if (!empty($attrs['className'])) {
            $classNames = explode(' ', $attrs['className']);
            foreach ($classNames as $idx => $className) {
                if (preg_match('/^has-(\w+)-font-size$/', $className, $matches)) {
                    $attrs['fontSize'] = $matches[1];
                    unset($classNames[$idx]);
                }
            }

            $attrs['className'] = implode(' ', $classNames);
        }

        if (in_array($block['blockName'], ['core/heading', 'core/group'])) {
            if (preg_match('/ id="([^"]+)"/', $block['innerHTML'], $matches)) {
                $attrs['anchor'] = $matches[1];
            }
        }

        switch ($block['blockName']) {
            case 'core/video':
                if (preg_match('/ src="([^"]+)"/', $block['innerHTML'], $matches)) {
                    $attrs['src'] = $matches[1];
                }
                break;
            case 'core/image':
                if (preg_match('/ src="([^"]+)"/', $block['innerHTML'], $matches)) {
                    $attrs['url'] = $matches[1];
                }
                break;
            case 'core/media-text':
                if (preg_match('/ src="([^"]+)"/', $block['innerHTML'], $matches)) {
                    $attrs['mediaUrl'] = $matches[1];
                }
                break;
            case 'core/heading':
            case 'core/paragraph':
                $attrs['placeholder'] = trim(strip_tags(str_replace(['<br>', '<br/>'], "\n", $block['innerHTML'])));
                break;
            case 'core/button':
                $attrs['placeholder'] = trim(strip_tags(str_replace(['<br>', '<br/>'], "\n", $block['innerHTML'])));
                if (preg_match('/ href="([^"]+)"/', $block['innerHTML'], $matches)) {
                    $attrs['url'] = $matches[1];
                }
                break;
        }

        $attrs = apply_filters('wp-gutenberg-templates/debugbar/blocks/attrs', $attrs, $block);

        return $attrs;
    }
}
