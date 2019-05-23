define([
  "core/str",
  "filter_teamwork/ajax",
  "filter_teamwork/popup",
  "filter_teamwork/render",
  "filter_teamwork/dragula",
  "filter_teamwork/skin"
], function(str, ajax, popup, render, drag, skin) {
  `use strict`;

  // const mainBlock = document.querySelector(`#teamwork`);
  const mainBlock = document.querySelector(`body`);

  const set_teamwork_enable = (courseid, activityid, moduletype, callback) => {
    ajax.data = {
      method: "set_teamwork_enable",
      courseid: courseid,
      activityid: activityid,
      moduletype: moduletype
    };
    ajax.run();
  };

  const set_access_to_student = (courseid, activityid, moduletype, target) => {
    const access = target.checked;

    ajax.data = {
      method: "set_access_to_student",
      access: access,
      courseid: courseid,
      activityid: activityid,
      moduletype: moduletype
    };
    ajax.send();
  };

  const add_new_card = (
    courseid,
    activityid,
    moduletype,
    selectgroupid,
    callback
  ) => {
    ajax.data = {
      method: `add_new_card`,
      courseid: courseid,
      activityid: activityid,
      moduletype: moduletype,
      selectgroupid: selectgroupid
    };
    ajax.run(callback);
  };

  const delete_card = (teamid, courseid, activityid, moduletype, callback) => {
    ajax.data = {
      method: `delete_card`,
      courseid: courseid,
      activityid: activityid,
      moduletype: moduletype,
      teamid: teamid
    };
    ajax.run(callback);
  };

  const show_random_popup = () => {
    ajax.data = {
      method: `show_random_popup`
    };
    ajax.runPopup();
  };

  /* render_student_settings_popup */
  const render_student_settings_popup = (activityid, moduletype) => {
    ajax.data = {
      method: `render_student_settings_popup`,
      activityid: activityid,
      moduletype: moduletype
    };
    ajax.runPopup();
    setTimeout(student_settings_enddate_state, 1000);
  };

  /* toggle actions on allow enddate checkbox status change in student settings popup */
  const student_settings_enddate_state = () => {
    const teamuserallowenddatechkbox = document.querySelector("#teamuserallowenddate");
    const allinputs = Array.from(
      document.querySelectorAll(".teamuserenddate-inputs-wrapper input, .teamuserenddate-inputs-wrapper select")
    );
    teamuserallowenddatechkbox.addEventListener("change", e => {
        if(e.target.checked){
          allinputs.forEach(item => {
              item.removeAttribute("disabled");
            });
          e.target.value = "1";
        } else {
          allinputs.forEach(item => {
            item.setAttribute("disabled", "disabled");
          });
          e.target.value = "0";
        }
      });
  };

  /* get data from form */
  const student_settings_popup_data = (courseid, activityid, moduletype) => {
    const popupForm = document.querySelector(".teamwork-modal");
    const teamNumbers = popupForm.querySelector(`#teamnumbers`).value;
    const teamUserNumbers = popupForm.querySelector(`#teamusernumbers`).value;
    const teamUserendDate = popupForm.querySelector(
      "input[name=team-userend-date]"
    ).value;
    const teamUserendMonth = popupForm.querySelector(
      "select[name=team-userend-month]"
    ).value;
    const teamUserendYear = popupForm.querySelector(
      "input[name=team-userend-year]"
    ).value;
    const teamUserendHour = popupForm.querySelector(
      "input[name=team-userend-hour]"
    ).value;
    const teamUserenMinute = popupForm.querySelector(
      "input[name=team-userend-minute]"
    ).value;
    const teamuserallowenddate = popupForm.querySelector(
      "input[name=teamuserallowenddate]"
    ).value;
    ajax.data = {
      method: `student_settings_popup_data`,
      teamNumbers: teamNumbers,
      teamUserNumbers: teamUserNumbers,
      teamUserendDate: teamUserendDate,
      teamUserendMonth: teamUserendMonth,
      teamUserendYear: teamUserendYear,
      teamUserendHour: teamUserendHour,
      teamUserenMinute: teamUserenMinute,
      teamuserallowenddate: teamuserallowenddate,
      courseid: courseid,
      activityid: activityid,
      moduletype: moduletype
    };
    ajax.send();
  };

  const set_random_teams = (
    target,
    courseid,
    activityid,
    moduletype,
    selectgroupid,
    callback
  ) => {
    while (!target.classList.contains(`teamwork-modal`)) {
      target = target.parentNode;
    }
    const numberOfStudent = target.querySelector(`#student_number`).value;

    ajax.data = {
      method: `set_random_team`,
      numberOfStudent: numberOfStudent,
      courseid: courseid,
      activityid: activityid,
      moduletype: moduletype,
      selectgroupid: selectgroupid
    };
    ajax.run(callback);
  };

  const set_team_size = (target, callback) => {
    const numberOfStudent = target.value;

    ajax.data = {
      method: `set_number_of_student_each_team`,
      numberOfStudent: numberOfStudent
    };
    ajax.run(callback);
  };

  const set_new_team_name = target => {
    const cardid = target.dataset.team_id;
    const cardname = target.value;

    ajax.data = {
      method: "set_new_team_name",
      cardid: cardid,
      cardname: cardname
    };
    ajax.send();
  };

  // search student by name on student list
  const searchStudentByName = target => {
    const studentList = Array.from(
      document.querySelectorAll("#studentList div[data-student_id]")
    );
    const hiddenClass = `visuallyhidden`;
    const searchItem = target.value;

    if (!searchItem) {
      studentList.forEach(item => {
        item.classList.remove(hiddenClass);
      });
      return;
    }
    let value = new RegExp(`${searchItem}`, `i`);
    studentList.forEach(item => {
      if (item.innerHTML.search(value) >= 0) {
        item.classList.remove(hiddenClass);
      } else {
        item.classList.add(hiddenClass);
      }
    });
  };

  const searchInit = () => {
    // init search for student list
    const searchInput = mainBlock.querySelector(
      `input[data-handler = "search_student"]`
    );
    searchInput.addEventListener("input", function(e) {
      searchStudentByName(searchInput);
    });
  };

  const searchReset = target => {
    const studentList = Array.from(
      document.querySelectorAll("#studentList div[data-student_id]")
    );
    const hiddenClass = `visuallyhidden`;
    const searchInput = mainBlock.querySelector(
      `input[data-handler = "search_student"]`
    );

    searchInput.value = ``;
    studentList.forEach(item => {
      item.classList.remove(hiddenClass);
    });
  };

  return {
    init: function(courseid, activityid, moduletype, selectgroupid) {
      render.data = {
        sesskey: M.cfg.sesskey,
        courseid: courseid,
        activityid: activityid,
        moduletype: moduletype,
        selectgroupid: selectgroupid
      };
      // run and open filter window
      $("#open_filter").on("click", function() {
        render.mainBlock(searchInit);
      });

      document.addEventListener(`click`, function(event) {
        let target = event.target;
        while (target !== mainBlock) {
          // activate/diactivate teamwork
          if (target.dataset.handler === `teamwork_toggle`) {
            target.classList.toggle(`active`);
            // target.classList.toggle(`teamwork-btn-fixed`);

            $(".skin_shadow").toggleClass("skin_show");
            $(".skin_shadow").toggleClass("skin_hide");
            set_teamwork_enable(courseid, activityid, moduletype);
            return;
          }

          // close popups
          if (
            target.classList.contains(`close_popup`) ||
            target.classList.contains(`teamwork-modal_close`)
          ) {
            if (target.classList.contains(`stop-close`)) {
              return;
            }
            popup.remove();
            return;
          }

          // close skin popup
          if (target.classList.contains(`skin_close`)) {
            skin.remove();
            return;
          }

          // switch access to student
          /*  if (target.dataset.handler === `access_to_student`) {
                target.classList.toggle(`active`);
               set_access_to_student(courseid, activityid, moduletype, target);
                return
              } */

          // show show student_setting popup
          if (target.dataset.handler === `access_to_student`) {
            if (target.classList.contains(`active`)) {
              target.classList.remove(`active`);
              set_access_to_student(courseid, activityid, moduletype, target);
            } else {
              target.classList.add(`active`);
              render_student_settings_popup(activityid, moduletype);
              set_access_to_student(courseid, activityid, moduletype, target);
            }
          }

          // get data from popup form
          if (target.dataset.handler === `get_popup_data`) {
            event.preventDefault();
            student_settings_popup_data(courseid, activityid, moduletype);
            popup.remove();
            return;
          }

          // open select group menu
          if (target.dataset.handler === `open_group_selection`) {
            $(target)
              .next()
              .slideToggle();
            return;
          }
          // handle select group
          if (target.dataset.handler === `select_groups`) {
            let text = document.querySelector('html[lang="en"]')
              ? "choose grous"
              : "בחר קבוצה";
            if (target.classList.contains(`selected`)) return;
            target.classList.toggle(`selected`);
            $('div[data-handler="open_group_selection"]').html(
              target.classList.contains(`selected`) ? target.innerHTML : text
            );

            $(target)
              .siblings()
              .removeClass("selected");

            let result = [];
            let val = Array.from(target.parentNode.children);
            val.forEach(item => {
              if (item.classList.contains(`selected`)) {
                result.push(item.dataset.value);
              }
            });
            $(target)
              .parent()
              .slideToggle();
            selectgroupid = JSON.stringify(result);
            render.data.selectgroupid = JSON.stringify(result);
            render.studentList();
            render.teamsCard();
            //render.teamsCard();
            return;
          }

          // add new team card
          if (target.dataset.handler === `add_new_teamcard`) {
            add_new_card(
              courseid,
              activityid,
              moduletype,
              selectgroupid,
              function() {
                render.setDefaultData();
                render.studentList();
                render.teamsCard();
              }
            );

            return;
          }

          // delete team card
          if (target.dataset.handler === `delete_teamcard`) {
            let teamid = target.dataset.remove_team_id;
            delete_card(teamid, courseid, activityid, moduletype, function() {
              render.setDefaultData();
              render.teamsCard();
              render.studentList();
            });

            return;
          }

          // show popup to determine the random number of students on the each team
          if (target.dataset.handler === `random_popup`) {
            show_random_popup();
            return;
          }

          // set random teams
          if (target.dataset.handler === `random`) {
            set_random_teams(
              target,
              courseid,
              activityid,
              moduletype,
              render.data.selectgroupid,
              function() {
                render.teamsCard();
                render.studentList();
              }
            );
            return;
          }

          // set teame size
          if (target.dataset.handler === `team_size`) {
            set_team_size(target, function() {
              render.data = {
                sesskey: M.cfg.sesskey
              };
              render.teamsCard();
            });
            return;
          }

          // search reset
          if (target.dataset.handler === `search_reset`) {
            searchReset(target);
            return;
          }

          target = target.parentNode;
        }
      });
      // init drug and drop events
      drag.startDrag();

      // change team name
      document.addEventListener("change", function(event) {
        if (event.target.dataset.handler === `input_team_name`) {
          set_new_team_name(event.target);
        }
      });

      // close all popups by esc
      document.addEventListener("keydown", function(event) {
        if (event.keyCode === 27) {
          skin.remove();
          popup.remove();
        }
      });
    }
  };
});
