
define(['filter_teamwork/ajax', 'filter_teamwork/render'], function(ajax, render) {
`use strict`;

  const renderPageAfterDrag = (el, callback) => {

    const allTeamsBlocks = Array.from(document.querySelectorAll(`div[data-team_id]`));
    const allTeams = [];
    const draguserid = el.dataset.student_id;

    let teamid;
    allTeamsBlocks.forEach((item)=>{
      let team = {};
      team.teamid = item.dataset.team_id;
      team.studentid = [];

      let allStudents = Array.from(item.querySelectorAll(`.teamwork_student`));
      allStudents.forEach((student)=>{
        team.studentid.push(student.dataset.student_id);
      });
      allTeams.push(team);
    });

    ajax.data =  {
      draguserid: draguserid,
      method: 'drag_student_card',
      newTeams : JSON.stringify(allTeams),
      activityid: render.data.activityid,
      moduletype: render.data.moduletype,
      courseid: render.data.courseid,
      selectgroupid: render.data.selectgroupid
    };

    ajax.run(callback);

  }

  const checkOverflowAndRender = (el, target, source) => {

    let maxCount = source.dataset.max_count;

    if (target.childElementCount === maxCount) {
      target.classList.add(`stop-drag`);
    }
    if (source.childElementCount < maxCount) {
      source.classList.remove(`stop-drag`);
    }

    renderPageAfterDrag(el, function(){
      render.setDefaultData();
      render.studentList();
      render.teamsCard();
    });

  }

  const drag = {

    startDrag: function() {
      dragula({
        isContainer: function (el) {
          return el.classList.contains(`draggable`);
        },
        accepts: function (el, target) {
          // if (el.classList.contains(`stop-drag-item`)) {
          //   return false;
          // }
          if (!target.classList.contains(`stop-drag`)) {
            return target;
          }

        },
        invalid: function (el, handle) {
          if(el.classList.contains(`stop-drag-item`)){
            return true;
          }
        }
      }).on('drop', checkOverflowAndRender)
    }
  }

    return drag;
});
