// Detect passive event listener support
let supportsPassive = false;
/* eslint-disable */
try {
  window.addEventListener('test', null, Object.defineProperty({}, 'passive', {
    get: () => {
      supportsPassive = true;
    }
  }));
} catch (e) {}
/* eslint-enable */

class DOMEvents {
  constructor() {
    this._pool = [];
  }

  /**
   * Adds event listeners
   *
   * @param {DOMElement} target
   * @param {String} type Can be multiple, separated by space.
   * @param {Function} listener
   * @param {Boolean} passive
   */
  add(target, type, listener, passive) {
    this._toggleListener(target, type, listener, passive);
  }

  /**
   * Removes event listeners
   *
   * @param {DOMElement} target
   * @param {String} type
   * @param {Function} listener
   * @param {Boolean} passive
   */
  remove(target, type, listener, passive) {
    this._toggleListener(target, type, listener, passive, true);
  }

  /**
   * Removes all bound events
   */
  removeAll() {
    this._pool.forEach((poolItem) => {
      this._toggleListener(
        poolItem.target,
        poolItem.type,
        poolItem.listener,
        poolItem.passive,
        true,
        true
      );
    });
    this._pool = [];
  }

  /**
   * Adds or removes event
   *
   * @param {DOMElement} target
   * @param {String} type
   * @param {Function} listener
   * @param {Boolean} passive
   * @param {Boolean} unbind Whether the event should be added or removed
   * @param {Boolean} skipPool Whether events pool should be skipped
   */
  _toggleListener(target, type, listener, passive, unbind, skipPool) {
    if (!target) {
      return;
    }

    const methodName = (unbind ? 'remove' : 'add') + 'EventListener';
    type = type.split(' ');
    type.forEach((eType) => {
      if (eType) {
        // Events pool is used to easily unbind all events when photoswipe is closed,
        // so developer doesn't need to do this manually
        if (!skipPool) {
          if (unbind) {
            // Remove from the events pool
            this._pool = this._pool.filter((poolItem) => {
              return poolItem.type !== eType
                || poolItem.listener !== listener
                || poolItem.target !== target;
            });
          } else {
            // Add to the events pool
            this._pool.push({
              target,
              type: eType,
              listener,
              passive
            });
          }
        }


        // most photoswipe events call preventdefault,
        // and we do not need browser to scroll the page
        const eventOptions = supportsPassive ? { passive: (passive || false) } : false;

        target[methodName](
          eType,
          listener,
          eventOptions
        );
      }
    });
  }
}

export default DOMEvents;
