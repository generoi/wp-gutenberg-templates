import { select, subscribe, dispatch } from '@wordpress/data';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import apiRequest from '@wordpress/apiRequest';
import { synchronizeBlocksWithTemplate, doBlocksMatchTemplate } from '@wordpress/blocks';

const SYNCHRONIZE_TEMPLATE_NOTICE_ID = 'SYNCHRONIZE_TEMPLATE_NOTICE_ID';

class GutenbergTemplates {
  constructor() {
    this.previousTemplate = null;
    this.template = null;
    this.initialLoad = true;

    subscribe(() => {
      this.initialLoad = false;
      this.subscribe();
    });
    // Trigger initial load
    this.subscribe();
  }

  subscribe() {
    const newTemplate = select('core/editor').getEditedPostAttribute('template');

    if (newTemplate !== this.template) {
      this.previousTemplate = this.template;
      this.template = newTemplate;

      if (newTemplate) {
        this.changeTemplate();
      } else if (this.previousTemplate !== null) {
        // If we're going back to default template.
        dispatch('core/editor').updateEditorSettings({templateLock: false});
      }
    }
  }

  changeTemplate() {
    const { resetBlocks, editPost, updateEditorSettings } = dispatch('core/editor');
    const currentBlocks = select('core/editor').getBlocks();

    apiRequest({ path: '/gutenberg-templates/v1/template', data: {template: this.template} }).then(config => {
      const template = config.template;
      const templateLock = config.template_lock;
      const isValidTemplate = !currentBlocks.length || doBlocksMatchTemplate(currentBlocks, template);

      const synchronizeTemplate = () => {
        resetBlocks(synchronizeBlocksWithTemplate(currentBlocks, template));
        updateEditorSettings({ templateLock });
      };

      const denySynchronization = () => {
        // If it's a locked template, revert the setting.
        if (templateLock === 'all') {
          editPost({ template: this.previousTemplate });
        }
        removeNotice(SYNCHRONIZE_TEMPLATE_NOTICE_ID);
      };

      if (isValidTemplate) {
        synchronizeTemplate();
      } else if (this.wasDefaultTemplate()) {
        if (window.confirm(__('The content of your post doesn\'t match the assigned template. Resetting the template may result in loss of content, do you want to continue?', 'wp-gutenberg-templates'))) {
          synchronizeTemplate();
        }
      }
    });
  }

  wasDefaultTemplate() {
    return this.previousTemplate === '' && !this.initialLoad;
  }
}

new GutenbergTemplates;
