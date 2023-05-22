/**
 * Gestures class bind touch, pointer or mouse events
 * and emits drag to drag-handler and zoom events zoom-handler.
 *
 * Drag and zoom events are emited in requestAnimationFrame,
 * and only when one of pointers was actually changed.
 */
import {
  equalizePoints, pointsEqual, getDistanceBetween
} from '../util/util.js';

import DragHandler from './drag-handler.js';
import ZoomHandler from './zoom-handler.js';
import TapHandler from './tap-handler.js';

// How far should user should drag
// until we can determine that the gesture is swipe and its direction
const AXIS_SWIPE_HYSTERISIS = 10;
//const PAN_END_FRICTION = 0.35;

const DOUBLE_TAP_DELAY = 300; // ms
const MIN_TAP_DISTANCE = 25; // px

class Gestures {
  constructor(pswp) {
    this.pswp = pswp;


    // point objects are defined once and reused
    // Photoswipe keeps track only of two pointers, others are ignored
    this.p1 = {}; // the first pressed pointer
    this.p2 = {}; // the second pressed pointer
    this.prevP1 = {};
    this.prevP2 = {};
    this.startP1 = {};
    this.startP2 = {};
    this.velocity = {};

    this._lastStartP1 = {};
    this._intervalP1 = {};
    this._numActivePoints = 0;
    this._ongoingPointers = [];

    this._touchEventEnabled = 'ontouchstart' in window;
    this._pointerEventEnabled = !!(window.PointerEvent);
    this.supportsTouch = this._touchEventEnabled
                          || (this._pointerEventEnabled && navigator.maxTouchPoints > 1);

    if (!this.supportsTouch) {
      // disable pan to next slide for non-touch devices
      pswp.options.allowPanToNext = false;
    }

    this.drag = new DragHandler(this);
    this.zoomLevels = new ZoomHandler(this);
    this.tapHandler = new TapHandler(this);

    pswp.on('bindEvents', () => {
      pswp.events.add(pswp.scrollWrap, 'click', e => this._onClick(e));

      if (this._pointerEventEnabled) {
        this._bindEvents('pointer', 'down', 'up', 'cancel');
      } else if (this._touchEventEnabled) {
        this._bindEvents('touch', 'start', 'end', 'cancel');

        // In previous versions we also bound mouse event here,
        // in case device supports both touch and mouse events,
        // but newer versions of browsers now support pointerevent.

        // on ios10 if you bind touchmove/end after touchstart,
        // and you don't preventdefault touchstart (which photoswipe does),
        // preventdefault will have no effect on touchmove and touchend.
        // Unless you bind it previously.
        pswp.scrollWrap.ontouchmove = () => {}; // eslint-disable-line
        pswp.scrollWrap.ontouchend = () => {}; // eslint-disable-line
      } else {
        this._bindEvents('mouse', 'down', 'up');
      }
    });
  }

  _bindEvents(pref, down, up, cancel) {
    const { pswp } = this;
    const { events } = pswp;

    const cancelEvent = cancel ? pref + cancel : '';

    events.add(pswp.scrollWrap, pref + down, this.onPointerDown.bind(this));
    events.add(window, pref + 'move', this.onPointerMove.bind(this));
    events.add(window, pref + up, this.onPointerUp.bind(this));
    if (cancelEvent) {
      events.add(pswp.scrollWrap, cancelEvent, this.onPointerUp.bind(this));
    }
  }


  onPointerDown(e) {
    // We do not call preventdefault for touch events
    // to allow browser to show native dialog on longpress
    // (the one that allows to save image or open it in new tab).
    //
    // Desktop safari allows to drag images when preventdefault isn't called on mousedown,
    // even though preventdefault is called on mousemove. that's why we preventdefault mousedown.
    let isMousePointer;
    if (e.type === 'mousedown' || e.pointerType === 'mouse') {
      isMousePointer = true;
    }

    // Allow dragging only via left mouse button.
    // http://www.quirksmode.org/js/events_properties.html
    // https://developer.mozilla.org/en-us/docs/web/api/event.button
    if (isMousePointer && e.button > 0) {
      return;
    }

    const { pswp } = this;

    // if photoswipe is opening or closing
    if (!pswp.opener.isOpen) {
      e.preventDefault();
      return;
    }

    if (pswp.dispatch('pointerDown', { originalEvent: e }).defaultPrevented) {
      return;
    }

    if (isMousePointer) {
      pswp.mouseDetected();

      // preventdefault mouse event to prevent
      // browser image drag feature
      this._preventPointerEventBehaviour(e);
    }

    pswp.animations.stopAll();

    this._updatePoints(e, 'down');

    this.pointerDown = true;

    if (this._numActivePoints === 1) {
      this.dragAxis = null;
      // we need to store initial point to determine the main axis,
      // drag is activated only after the axis is determined
      equalizePoints(this.startP1, this.p1);
    }

    if (this._numActivePoints > 1) {
      // Tap or double tap should not trigger if more than one pointer
      this._clearTapTimer();
      this.isMultitouch = true;
    } else {
      this.isMultitouch = false;
    }
  }

  onPointerMove(e) {
    e.preventDefault(); // always preventdefault move event

    if (!this._numActivePoints) {
      return;
    }

    this._updatePoints(e, 'move');

    if (this.pswp.dispatch('pointerMove', { originalEvent: e }).defaultPrevented) {
      return;
    }

    if (this._numActivePoints === 1 && !this.isDragging) {
      if (!this.dragAxis) {
        this._calculateDragDirection();
      }

      // Drag axis was detected, emit drag.start
      if (this.dragAxis && !this.isDragging) {
        if (this.isZooming) {
          this.isZooming = false;
          this.zoomLevels.end();
        }

        this.isDragging = true;
        this._clearTapTimer(); // Tap can not trigger after drag

        // Adjust starting point
        this._updateStartPoints();
        this._intervalTime = Date.now();
        //this._startTime = this._intervalTime;
        this._velocityCalculated = false;
        equalizePoints(this._intervalP1, this.p1);
        this.velocity.x = 0;
        this.velocity.y = 0;
        this.drag.start();

        this._rafStopLoop();
        this._rafRenderLoop();
      }
    } else if (this._numActivePoints > 1 && !this.isZooming) {
      this._finishDrag();

      this.isZooming = true;

      // Adjust starting points
      this._updateStartPoints();

      this.zoomLevels.start();

      this._rafStopLoop();
      this._rafRenderLoop();
    }
  }

  _finishDrag() {
    if (this.isDragging) {
      this.isDragging = false;

      // Try to calculate velocity,
      // if it wasn't calculated yet in drag.change
      if (!this._velocityCalculated) {
        this._updateVelocity(true);
      }

      this.drag.end();
      this.dragAxis = null;
    }
  }


  onPointerUp(e) {
    if (!this._numActivePoints) {
      return;
    }

    this._updatePoints(e, 'up');

    if (this.pswp.dispatch('pointerUp', { originalEvent: e }).defaultPrevented) {
      return;
    }

    if (this._numActivePoints === 0) {
      this.pointerDown = false;
      this._rafStopLoop();

      if (this.isDragging) {
        this._finishDrag();
      } else if (!this.isZooming && !this.isMultitouch) {
        //this.zoomLevels.correctZoomPan();
        this._finishTap(e);
      }
    }

    if (this._numActivePoints < 2 && this.isZooming) {
      this.isZooming = false;
      this.zoomLevels.end();

      if (this._numActivePoints === 1) {
        // Since we have 1 point left, we need to reinitiate drag
        this.dragAxis = null;
        this._updateStartPoints();
      }
    }
  }


  _rafRenderLoop() {
    if (this.isDragging || this.isZooming) {
      this._updateVelocity();

      if (this.isDragging) {
        // make sure that pointer moved since the last update
        if (!pointsEqual(this.p1, this.prevP1)) {
          this.drag.change();
        }
      } else /* if (this.isZooming) */ {
        if (!pointsEqual(this.p1, this.prevP1)
            || !pointsEqual(this.p2, this.prevP2)) {
          this.zoomLevels.change();
        }
      }

      this._updatePrevPoints();
      this.raf = requestAnimationFrame(this._rafRenderLoop.bind(this));
    }
  }

  /**
   * Update velocity at 50ms interval
   */
  _updateVelocity(force) {
    const time = Date.now();
    const duration = time - this._intervalTime;

    if (duration < 50 && !force) {
      return;
    }


    this.velocity.x = this._getVelocity('x', duration);
    this.velocity.y = this._getVelocity('y', duration);

    this._intervalTime = time;
    equalizePoints(this._intervalP1, this.p1);
    this._velocityCalculated = true;
  }

  _finishTap(e) {
    const { mainScroll } = this.pswp;

    // Do not trigger tap events if main scroll is shifted
    if (mainScroll.isShifted()) {
      // restore main scroll position
      // (usually happens if stopped in the middle of animation)
      mainScroll.moveIndexBy(0, true);
      return;
    }

    // Do not trigger tap for touchcancel or pointercancel
    if (e.type.indexOf('cancel') > 0) {
      return;
    }

    // Trigger click instead of tap for mouse events
    if (e.type === 'mouseup' || e.pointerType === 'mouse') {
      this.tapHandler.click(this.startP1, e);
      return;
    }

    // Disable delay if there is no doubletapaction
    const tapDelay = this.pswp.options.doubleTapAction ? DOUBLE_TAP_DELAY : 0;

    // If taptimer is defined - we tapped recently,
    // check if the current tap is close to the previous one,
    // if yes - trigger double tap
    if (this._tapTimer) {
      this._clearTapTimer();
      // Check if two taps were more or less on the same place
      if (getDistanceBetween(this._lastStartP1, this.startP1) < MIN_TAP_DISTANCE) {
        this.tapHandler.doubleTap(this.startP1, e);
      }
    } else {
      equalizePoints(this._lastStartP1, this.startP1);
      this._tapTimer = setTimeout(() => {
        this.tapHandler.tap(this.startP1, e);
        this._clearTapTimer();
      }, tapDelay);
    }
  }

  _clearTapTimer() {
    if (this._tapTimer) {
      clearTimeout(this._tapTimer);
      this._tapTimer = null;
    }
  }

  /**
   * Get velocity for axis
   *
   * @param {Number} axis
   * @param {Number} duration
   */
  _getVelocity(axis, duration) {
    // displacement is like distance, but can be negative.
    const displacement = this.p1[axis] - this._intervalP1[axis];

    if (Math.abs(displacement) > 1 && duration > 5) {
      return displacement / duration;
    }

    return 0;
  }

  _rafStopLoop() {
    if (this.raf) {
      cancelAnimationFrame(this.raf);
      this.raf = null;
    }
  }

  // eslint-disable-next-line class-methods-use-this
  _preventPointerEventBehaviour(e) {
    // Todo find a way to disable e.preventdefault on some elements
    //      via event or some class or something
    e.preventDefault();
    return true;
  }

  /**
   * Parses and normalizes points from the touch, mouse or pointer event.
   * Updates p1 and p2.
   *
   * @param {Event} e
   * @param {String} pointerType Normalized pointer type ('up', 'down' or 'move')
   */
  _updatePoints(e, pointerType) {
    if (this._pointerEventEnabled) {
      // Try to find the current pointer in ongoing pointers by its id
      const pointerIndex = this._ongoingPointers.findIndex((ongoingPoiner) => {
        return ongoingPoiner.id === e.pointerId;
      });

      if (pointerType === 'up' && pointerIndex > -1) {
        // release the pointer - remove it from ongoing
        this._ongoingPointers.splice(pointerIndex, 1);
      } else if (pointerType === 'down' && pointerIndex === -1) {
        // add new pointer
        this._ongoingPointers.push(this._convertEventPosToPoint(e, {}));
      } else if (pointerIndex > -1) {
        // update existing pointer
        this._convertEventPosToPoint(e, this._ongoingPointers[pointerIndex]);
      }

      this._numActivePoints = this._ongoingPointers.length;

      // update points that photoswipe uses
      // to calculate position and scale
      if (this._numActivePoints > 0) {
        equalizePoints(this.p1, this._ongoingPointers[0]);
      }

      if (this._numActivePoints > 1) {
        equalizePoints(this.p2, this._ongoingPointers[1]);
      }
    } else {
      this._numActivePoints = 0;
      if (e.type.indexOf('touch') > -1) {
        // Touch event
        // https://developer.mozilla.org/en-us/docs/web/api/touchevent
        if (e.touches && e.touches.length > 0) {
          this._convertEventPosToPoint(e.touches[0], this.p1);
          this._numActivePoints++;
          if (e.touches.length > 1) {
            this._convertEventPosToPoint(e.touches[1], this.p2);
            this._numActivePoints++;
          }
        }
      } else {
        // Mouse event
        this._convertEventPosToPoint(e, this.p1);
        if (pointerType === 'up') {
          // clear all points on mouseup
          this._numActivePoints = 0;
        } else {
          this._numActivePoints++;
        }
      }
    }
  }

  // update points that were used during previous raf tick
  _updatePrevPoints() {
    equalizePoints(this.prevP1, this.p1);
    equalizePoints(this.prevP2, this.p2);
  }

  // update points at the start of gesture
  _updateStartPoints() {
    equalizePoints(this.startP1, this.p1);
    equalizePoints(this.startP2, this.p2);
    this._updatePrevPoints();
  }

  _calculateDragDirection() {
    if (this.pswp.mainScroll.isShifted()) {
      // if main scroll position is shifted – direction is always horizontal
      this.dragAxis = 'x';
    } else {
      // calculate delta of the last touchmove tick
      const diff = Math.abs(this.p1.x - this.startP1.x) - Math.abs(this.p1.y - this.startP1.y);

      if (diff !== 0) {
        // check if pointer was shifted horizontally or vertically
        const axisToCheck = diff > 0 ? 'x' : 'y';

        if (Math.abs(this.p1[axisToCheck] - this.startP1[axisToCheck]) >= AXIS_SWIPE_HYSTERISIS) {
          this.dragAxis = axisToCheck;
        }
      }
    }
  }

  /**
   * Converts touch, pointer or mouse event
   * to PhotoSwipe point.
   *
   * @param {Event} e
   * @param {Point} p
   */
  _convertEventPosToPoint(e, p) {
    p.x = e.pageX - this.pswp.offset.x;
    p.y = e.pageY - this.pswp.offset.y;

    // e.pointerid can be zero
    if (e.pointerId !== undefined) {
      p.id = e.pointerId;
    } else if (e.identifier !== undefined) {
      p.id = e.identifier;
    }

    return p;
  }

  _onClick(e) {
    // Do not allow click event to pass through after drag
    if (this.pswp.mainScroll.isShifted()) {
      e.preventDefault();
      e.stopPropagation();
    }
  }
}

export default Gestures;
