import './style.scss';
import './editor.scss';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

registerBlockType('genero/example-block', {
  title: __('Example block', 'wp-gutenberg-boilerplate'),
  icon: 'email',
  category: 'embed',
  supports: {
    html: false,
    align: ['center', 'wide', 'full'],
  },
  keywords: [
    __('example'),
  ],

  edit: (props) => {
    return (
      <div className={ props.className }>
          Example block
      </div>
    );
  },

  save: (props) => {
    return (
      <div className={ props.className }>
          Example block
      </div>
    );
  },
});
