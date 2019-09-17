import { select, subscribe, dispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import apiRequest from '@wordpress/apiRequest';
import { synchronizeBlocksWithTemplate, doBlocksMatchTemplate } from '@wordpress/blocks';

const SYNCHRONIZE_TEMPLATE_NOTICE_ID = 'SYNCHRONIZE_TEMPLATE_NOTICE_ID';

class GutenbergTemplates {
  constructor() {
    this.previousTemplateName = null;
    this.templateName = undefined;
    this.template = null;
    this.templateLock = null;

    // Subscribe to changes
    subscribe(this.subscribe.bind(this));
  }

  subscribe() {
    const { getTemplate, getTemplateLock } = select('core/block-editor');
    const { updateSettings, setTemplateValidity } = dispatch('core/block-editor');
    const newTemplateName = select('core/editor').getEditedPostAttribute('template');

    // Not known yet
    if (newTemplateName === undefined) {
      return;
    }

    // This is the initial template on editor load
    if (this.templateName === undefined) {
      this.templateName = newTemplateName;
      return;
    }

    // The template has changed
    if (newTemplateName !== this.templateName) {
      this.previousTemplateName = this.templateName;
      this.templateName = newTemplateName;

      // An actual template was set
      if (newTemplateName) {
        this.changeTemplate(this.templateName);
        return;
      }

      // We're setting the Default template.
      if (newTemplateName === '') {
        this.templateLock = false;
        updateSettings({templateLock: this.templateLock});
        setTemplateValidity(true);
      }
    }

    if (this.template !== null) {
      const template = getTemplate();
      if (this.template !== template) {
        updateSettings({template: this.template});
      }
    }

    if (this.templateLock !== null) {
      const templateLock = getTemplateLock();
      if (this.templateLock !== templateLock) {
        updateSettings({templateLock: this.templateLock});
      }
    }
  }

  getTemplate(templateName, callback) {
    apiRequest({ path: '/gutenberg-templates/v1/template', data: {template: templateName} }).then(config => {
      const template = config.template;
      const templateLock = config.template_lock;

      callback({template, templateLock});
    });
  }

  changeTemplate(templateName) {
    this.getTemplate(templateName, ({template, templateLock}) => {
      const { resetBlocks, updateSettings } = dispatch('core/block-editor');
      const { createWarningNotice, removeNotice } = dispatch('core/notices');
      const currentBlocks = select('core/block-editor').getBlocks();
      const isBlocksValidToTemplate = (
        !template ||
        doBlocksMatchTemplate(currentBlocks, template)
      );

      const synchronizeTemplate = () => {
        resetBlocks(synchronizeBlocksWithTemplate(currentBlocks, template));
        updateSettings({ template, templateLock });

        this.template = template;
        this.templateLock = templateLock;
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
    dispatch('core/editor').editPost({ template: this.previousTemplateName });
  }
}

new GutenbergTemplates;
