/**
 * @file
 * Browser version of plugin scripts, includign polyfills and external
 * libraries.
 */

import PluginComponent from './plugin';
import objectAssign from 'es6-object-assign';

objectAssign.polyfill();
window.PluginComponent = new PluginComponent();
