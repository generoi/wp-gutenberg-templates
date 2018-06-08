const { select, subscribe, dispatch } = wp.data;
const apiRequest = wp.apiRequest;
const { synchronizeBlocksWithTemplate } = wp.blocks;

class GutenbergTemplates {
  constructor() {
    this.template = null;
    subscribe(this.subscribe.bind(this));
  }

  subscribe() {
    const newTemplate = select('core/editor').getEditedPostAttribute('template');

    if (newTemplate && newTemplate !== this.template) {
      this.template = newTemplate;
      this.changeTemplate();
    }
  }

  changeTemplate() {
    const { resetBlocks } = dispatch('core/editor');
    const currentBlocks = select('core/editor').getBlocks();

    apiRequest({ path: '/gutenberg-templates/v1/template', data: {template: this.template} }).then(config => {
      resetBlocks(synchronizeBlocksWithTemplate(currentBlocks, config.template));
    });
  }
}

new GutenbergTemplates;
