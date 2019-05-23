define([
  'core/yui',
  'filter_teamwork/popup',
  'filter_teamwork/loading',

], function(Y, popup, loadingIcon) {
`use strict`;

  let ajax = {

    url: '/filter/teamwork/ajax/ajax.php',

    data: '',

    sesskey: M.cfg.sesskey,

    send: function(){
      this.data.sesskey = this.sesskey;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
              },
              failure: function () {
                popup.error();
              }
          }
      });

    },

    run: function(callback){
      this.data.sesskey = this.sesskey;
      loadingIcon.show();
      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                loadingIcon.remove();
                let result = JSON.parse(response.responseText);
                if (result.error) {
                  popup.textError = result.errormsg;
                  popup.error();
                  return;
                }
                if (callback) callback();

              },
              failure: function () {
                popup.error();
              }
          }
      });

    },

    runPopup: function(){

      let result;
      this.data.sesskey = this.sesskey;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {

                // popup.text = response.responseText;
                let result = JSON.parse(response.responseText);
                popup.textHead = result.header;
                popup.text = result.content;
                popup.show();
              },
              failure: function () {
                popup.error();
              }
          }
      });

    },

    setHTML: function(){
      this.data.sesskey = this.sesskey;
      const targetBlock = document.querySelector(this.data.target_block);
      loadingIcon.show();
      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                loadingIcon.remove();
                popup.remove();
                let result = JSON.parse(response.responseText);
                targetBlock.innerHTML = result.content;
              },
              failure: function () {
                popup.error();
              }
          }
      });
    },

  }

  return ajax

});
