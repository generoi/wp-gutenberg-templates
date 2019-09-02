import { select, subscribe, dispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import apiRequest from '@wordpress/apiRequest';
import { synchronizeBlocksWithTemplate, doBlocksMatchTemplate } from '@wordpress/blocks';

const SYNCHRONIZE_TEMPLATE_NOTICE_ID = 'SYNCHRONIZE_TEMPLATE_NOTICE_ID';

class GutenbergTemplates {
  constructor() {
    this.previousTemplate = null;
    this.template = undefined;

    // Subscribe to changes
    subscribe(this.subscribe.bind(this));
  }

  subscribe() {
    const newTemplate = select('core/editor').getEditedPostAttribute('template');
    // Not known yet
    if (newTemplate === undefined) {
      return;
    }

    // This is the initial template on editor load
    if (this.template === undefined) {
      this.template = newTemplate;

      this.defaultTemplate = select('core/block-editor').getTemplate();
      this.defaultTemplateLock = select('core/block-editor').getTemplateLock();

      if (this.template) {
        this.setInitialTemplate(this.template);
      }
      return;
    }

    // The template has changed
    if (newTemplate !== this.template) {
      this.previousTemplate = this.template;
      this.template = newTemplate;

      // An actual template was set
      if (newTemplate) {
        this.changeTemplate(this.template);
        return;
      }

      // We're setting the Default template.
      if (newTemplate === '') {
        const { updateSettings, setTemplateValidity } = dispatch('core/block-editor');

        updateSettings({templateLock: this.defaultTemplateLock, template: this.defaultTemplate});
        setTemplateValidity(true);
      }
    }
  }

  setInitialTemplate(templateName) {
    const { updateSettings, setTemplateValidity } = dispatch('core/block-editor');

    apiRequest({ path: '/gutenberg-templates/v1/template', data: {template: templateName} }).then(config => {
      const currentBlocks = select('core/block-editor').getBlocks();
      const template = config.template;
      const templateLock = config.template_lock;
      const isBlocksValidToTemplate = (
        !template ||
        templateLock !== 'all' ||
        doBlocksMatchTemplate(currentBlocks, template)
      );

      updateSettings({
        templateLock,
        template
      });
      setTemplateValidity(isBlocksValidToTemplate);
    });
  }

  changeTemplate(templateName) {
    const { resetBlocks, updateSettings } = dispatch('core/block-editor');
    const { createWarningNotice, removeNotice } = dispatch('core/notices');

    apiRequest({ path: '/gutenberg-templates/v1/template', data: {template: templateName} }).then(config => {
      const currentBlocks = select('core/block-editor').getBlocks();
      const template = config.template;
      const templateLock = config.template_lock;

      const isBlocksValidToTemplate = (
        !template ||
        doBlocksMatchTemplate(currentBlocks, template)
      );

      const synchronizeTemplate = () => {
        resetBlocks(synchronizeBlocksWithTemplate(currentBlocks, template));
        updateSettings({ template, templateLock });
      };

      const denySynchronization = () => {
        // If it's a locked template, revert the setting.
        if (templateLock === 'all') {
          this.revertTemplate();
        }
        removeNotice(SYNCHRONIZE_TEMPLATE_NOTICE_ID);
      };

      const confirmSynchronization = () => {
        if (window.confirm(__('Resetting the template may result in loss of content, do you want to continue?', 'wp-gutenberg-templates'))) {
          synchronizeTemplate();
        }
        removeNotice(SYNCHRONIZE_TEMPLATE_NOTICE_ID);
      };

      // The template is valid, just synchronize it and set the lock.
      if (isBlocksValidToTemplate) {
        synchronizeTemplate();
        return;
      }

      // Template is not valid, confirm what to do
      createWarningNotice(
        __('The content of your post doesn\'t match the assigned template.', 'wp-gutenberg-templates'),
        {
          actions: [
            { label: __('Keep it as is', 'wp-gutenberg-templates'), onClick: denySynchronization },
            { label: __('Reset the template', 'wp-gutenberg-templates'), onClick: confirmSynchronization, className: 'is-primary' },
          ],
          isDismissible: true, // to make it sticky, dismissing doesnt trigger deny though.
          id: SYNCHRONIZE_TEMPLATE_NOTICE_ID,
        }
      );
    });
  }

  revertTemplate() {
    dispatch('core/editor').editPost({ template: this.previousTemplate });
  }
}

new GutenbergTemplates;
