import { createElement } from '../util/util.js';

function addElementHTML(htmlData) {
  if (typeof htmlData === 'string') {
    // Allow developers to provide full svg,
    // For example:
    // <svg viewbox="0 0 32 32" width="32" height="32" aria-hidden="true" class="pswp__icn">
    //   <path d="..." />
    //   <circle ... />
    // </svg>
    // Can also be any html string.
    return htmlData;
  }

  if (!htmlData || !htmlData.isCustomSVG) {
    return '';
  }

  const svgData = htmlData;
  let out = '<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 %d %d" width="%d" height="%d">';
  out = out.split('%d').join(svgData.size || 32); // replace all %d with size

  // Icons may contain outline/shadow,
  // to make it we "clone" base icon shape and add border to it.
  // Icon itself and border are styled via css.
  //
  // Property shadowid defines id of element that should be cloned.
  if (svgData.outlineID) {
    out += '<use class="pswp__icn-shadow" xlink:href="#' + svgData.outlineID + '"/>';
  }

  out += svgData.inner;

  out += '</svg>';

  return out;
}

class UIElement {
  constructor(pswp, data) {
    const name = data.name || data.className;
    let elementHTML = data.html;

    if (pswp.options[name] === false) {
      // exit if element is disabled from options
      return;
    }

    // Allow to override svg icons from options
    if (typeof pswp.options[name + 'SVG'] === 'string') {
      // arrowprevsvg
      // arrownextsvg
      // closesvg
      // zoomsvg
      elementHTML = pswp.options[name + 'SVG'];
    }

    pswp.dispatch('uiElementCreate', { data });

    let className = '';
    if (data.isButton) {
      className += 'pswp__button ';
      className += (data.className || `pswp__button--${data.name}`);
    } else {
      className += (data.className || `pswp__${data.name}`);
    }

    let element;
    let tagName = data.isButton ? (data.tagName || 'button') : (data.tagName || 'div');
    tagName = tagName.toLowerCase();
    element = createElement(className, tagName);

    if (data.isButton) {
      // create button element
      element = createElement(className, tagName);
      if (tagName === 'button') {
        element.type = 'button';
      }

      if (typeof pswp.options[name + 'Title'] === 'string') {
        element.title = pswp.options[name + 'Title'];
      } else if (data.title) {
        element.title = data.title;
      }
    }

    element.innerHTML = addElementHTML(elementHTML);

    if (data.onInit) {
      data.onInit(element, pswp);
    }

    if (data.onClick) {
      element.onclick = (e) => {
        if (typeof data.onClick === 'string') {
          pswp[data.onClick]();
        } else {
          data.onClick(e, element, pswp);
        }
      };
    }

    // Top bar is default position
    const appendTo = data.appendTo || 'bar';
    let container;
    if (appendTo === 'bar') {
      if (!pswp.topBar) {
        pswp.topBar = createElement('pswp__top-bar pswp__hide-on-close', false, pswp.scrollWrap);
      }
      container = pswp.topBar;
    } else {
      // element outside of top bar gets a secondary class
      // that makes element fade out on close
      element.classList.add('pswp__hide-on-close');

      if (appendTo === 'wrapper') {
        container = pswp.scrollWrap;
      } else {
        // root element
        container = pswp.element;
      }
    }

    container.appendChild(element);
  }
}

export default UIElement;
