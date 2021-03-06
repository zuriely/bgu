define(['core/str'], function(str) {
`use strict`;

let textError;
let closeBtn;

  str.get_strings([
      {key: 'close', component: 'local_social'},
      {key: 'error_message', component: 'local_social'}
  ]).done(function(){
    textError = M.util.get_string('error_message', 'local_social');
    closeBtn = M.util.get_string('close', 'local_social');
  });

  const mainBlock = document.querySelector(`body`);

  // const textError = M.util.get_string('error_message', 'local_social');
  // const closeBtn = M.util.get_string('close', 'local_social');

  const popup = {

    // mainBlock: `body`,
    textHead: ``,
    text: ``,
    textError: textError,

    show: function () {

      const popup = document.createElement(`div`);
        popup.innerHTML = `
          <div class = "teamwork-modal_header">
            <p class = "teamwork-modal_head">${this.textHead}</p>
            <span class = "teamwork-modal_close"></span>
          </div>
          <div class = "teamwork-modal_inner"></div>
        `;
        popup.classList.add(`teamwork-modal`);
      const popupInner = popup.querySelector(`.teamwork-modal_inner`);

        popupInner.innerHTML = this.text;
        this.remove();
        mainBlock.appendChild(popup);
    },

    error: function () {

      if (mainBlock.querySelector(`.teamwork-modal`)) {
        const errorBlock = document.createElement(`div`);
        errorBlock.classList.add(`teamwork-modal-error-abs`, `alert`, `alert-warning`);
        errorBlock.innerHTML = `
          <span>${this.textError}</span>
          <button class = "btn btn-error close_popup">${closeBtn}</button>
        `;
        mainBlock.querySelector(`.teamwork-modal`).appendChild(errorBlock);
      }else {
        const popup = document.createElement(`div`);
          popup.innerHTML = `
            <span>${this.textError}</span>
            <button class = "btn btn-error close_popup">${closeBtn}</button>
          `;
          popup.classList.add(`teamwork-modal`, `teamwork-modal-error`);

          this.remove();
          mainBlock.appendChild(popup);
      }

    },

    remove: function () {
      if(mainBlock.querySelector(`.teamwork-modal`)) {
        mainBlock.querySelector(`.teamwork-modal`).remove();
      }
    }

  };

  return popup

});
