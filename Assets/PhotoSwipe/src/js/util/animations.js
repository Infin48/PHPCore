import CSSAnimation from './css-animation.js';
import SpringAnimation from './spring-animation.js';

/**
 * Manages animations
 */

class Animations {
  constructor() {
    this.activeAnimations = [];
  }

  startSpring(props) {
    this._start(props, true);
  }

  startTransition(props) {
    this._start(props);
  }

  _start(props, isSpring) {
    let animation;
    if (isSpring) {
      animation = new SpringAnimation(props);
    } else {
      animation = new CSSAnimation(props);
    }

    this.activeAnimations.push(animation);
    animation.onFinish = () => this.stop(animation);

    return animation;
  }

  stop(animation) {
    animation.destroy();
    const index = this.activeAnimations.indexOf(animation);
    if (index > -1) {
      this.activeAnimations.splice(index, 1);
    }
  }

  stopAll() { // _stopallanimations
    this.activeAnimations.forEach((animation) => {
      animation.destroy();
    });
    this.activeAnimations = [];
  }

  /**
   * Stop all pan or zoom transitions
   */
  stopAllPan() {
    this.activeAnimations = this.activeAnimations.filter((animation) => {
      if (animation.props.isPan) {
        animation.destroy();
        return false;
      }

      return true;
    });
  }

  stopMainScroll() {
    this.activeAnimations = this.activeAnimations.filter((animation) => {
      if (animation.props.isMainScroll) {
        animation.destroy();
        return false;
      }

      return true;
    });
  }

  /**
   * Returns true if main scroll transition is running
   */
  // ismainscrollrunning() {
  //   return this.activeanimations.some((animation) => {
  //     return animation.props.ismainscroll;
  //   });
  // }\L

  /**
   * Returns true if any pan or zoom transition is running
   */
  isPanRunning() {
    return this.activeAnimations.some((animation) => {
      return animation.props.isPan;
    });
  }
}

export default Animations;
