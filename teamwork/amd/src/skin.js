define(['core/str'], function(str) {
`use strict`;

  str.get_strings([
      {key: 'close', component: 'local_social'},
      {key: 'error_message', component: 'local_social'}
  ]).done(function(){});

  const mainBlock = document.querySelector(`body`);
  // const style = document.createElement(`style`);

  const closeBtn = M.util.get_string('close', 'local_social');

  const skin = {

    content: ``,
    shadow: 'skin_hide',

    show: function () {

      const popup = document.createElement(`div`);
        popup.classList.add(`skin`, `shadow`);
        popup.innerHTML = `
          <div class = "skin_close"></div>
          <div class = "skin_inner"></div>
          <div class = "skin_shadow ${this.shadow}"></div>
        `;
      const popupInner = popup.querySelector(`.skin_inner`);
      popupInner.innerHTML = this.content;
      this.remove();
      // mainBlock.appendChild(style);
      mainBlock.appendChild(popup);
    },

    remove: function () {
      if(mainBlock.querySelector(`.skin`)) {
        mainBlock.querySelector(`.skin`).remove();
      }
    }

  };

  // style.innerHTML = `
  //   .skin {
  //     position: fixed;
  //     overflow: hidden;
  //     z-index: 10000;
  //     width: 80%;
  //     left: 10%;
  //     top: 12vh;
  //     background-color: #fff;
  //     border-radius: 5px;
  //     box-shadow: 0 0 10px 0 #7b7b7b;
  //     bottom: unset;
  //     min-height: 30vh;
  //   }
  //   .skin_shadow {
  //     position: absolute;
  //     top: 0;
  //     left: 0;
  //     right: 0;
  //     bottom: 0;
  //     margin: auto;
  //     background-color: rgba(165, 165, 165, 0.4);
  //     transition: .4s ease-in-out;
  //     transform-origin: 0 0;
  //   }
  //
  //   .skin_shadow.skin_show {
  //     transform: scaleY(1);
  //   }
  //
  //   .skin_shadow.skin_hide {
  //     transform: scaleY(0);
  //   }
  //
  //
  //   .teamwork.active + .skin_shadow{
  //     top: 100%;
  //   }
  //
  //   .skin_inner {
  //     padding: 10px 20px;
  //   }
  //
  //   .skin_close  {
  //     margin-left: auto;
  //   }
  //   .skin_close {
  //     cursor: pointer;
  //     position: absolute;
  //     z-index: 1;
  //     background-color: #fff;
  //     left: 25px;
  //     top: 25px;
  //     width: 33px;
  //     height: 33px;
  //     transition: .5s;
  //     background: none;
  //   }
  //   .skin_close:hover {
  //     transform: rotate(180deg);
  //   }
  //   .skin_close:before, .skin_close:after {
  //     position: absolute;
  //     left: 15px;
  //     content: ' ';
  //     height: 32px;
  //     width: 2px;
  //     background-color: #441e84;
  //   }
  //   .skin_close:before {
  //     transform: rotate(45deg);
  //   }
  //   .skin_close:after {
  //     transform: rotate(-45deg);
  //   }
  // `;

  return skin

});
