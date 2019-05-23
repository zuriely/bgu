define(['core/yui' , 'filter_teamwork/popup', 'filter_teamwork/ajax', 'filter_teamwork/skin'], function(Y, popup, ajax, skin) {
`use strict`;

  let render = {

    url: '/filter/teamwork/ajax/ajax.php',

    data: '',

    sesskey: M.cfg.sesskey,

    //Set default data
    setDefaultData: function(){
        let sesskey = this.data.sesskey;
        let courseid = this.data.courseid;
        let activityid = this.data.activityid;
        let moduletype = this.data.moduletype;
        let selectgroupid = this.data.selectgroupid;

        this.data = {
            sesskey: sesskey,
            courseid: courseid,
            activityid: activityid,
            moduletype: moduletype,
            selectgroupid: selectgroupid
        }

    },

    //Open main block
    mainBlock: function(searchInit){
      this.data.method = `render_teamwork_html`;
      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                let result = JSON.parse(response.responseText);
                // learnStat.innerHTML = result.content;

                skin.shadow = result.shadow;
                skin.content = result.content;
                skin.show();
                searchInit();
              },
              failure: function () {
                popup.error();
              }
          }
      });
    },


    studentList: function(){

      const targetBlock = document.querySelector(`#studentList`);
      this.data.method = `render_student_list`;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                let result = JSON.parse(response.responseText);
                targetBlock.innerHTML = result.content;
              },
              failure: function () {
                popup.error();
              }
          }
      });
    },


    teamsCard: function(){

      const targetBlock = document.querySelector(`#teamsCard`);
      this.data.method = `render_teams_card`;
      // this.data.sesskey = this.sesskey;

      Y.io(M.cfg.wwwroot + this.url, {
          method: 'POST',
          data: this.data,
          headers: {
              //'Content-Type': 'application/json'
          },
          on: {
              success: function (id, response) {
                let result = JSON.parse(response.responseText);
                targetBlock.innerHTML = result.content;
              },
              failure: function () {
                popup.error();
              }
          }
      });
    }

  }

  return render

});
