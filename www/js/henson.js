const convertFormToJSON = (form) => {
  let values = {}
  $(form)
    .find(":input")
    .each((i, inputElem) => {
      let [name, value] = [inputElem.name, inputElem.value]
      const inputType = inputElem.type
      if (`checkbox` == inputType) {
        value = inputElem.checked
      }
      const valueInt = parseInt(value)
      if (valueInt == value) {
        value = valueInt
      }
      values[name] = value
    })

  return values
}

const reloadTable = () => {
  $("#tasks").DataTable().rows().invalidate("data").draw(false)
}

const onAddNewTaskDone = (message) => {
  const id = message.id
  alert(`Task created: ${id}`)

  reloadTable()

  $("form#add-new-task :input").each((i, inputElem) => {
    inputElem.value = ``
  })
}

const onUpdateTaskDone = () => {
  $("div").modal("hide")
  reloadTable()
}

const onUpdateTaskFail = (httpStatus, errMsg) => {
  alert("Failed to update task:\n" + errMsg)
  if (403 == httpStatus) {
    $("div").modal("hide")
    $("#auth-modal").modal("show")
  }
}
const renderTheWasEdited = (data, type, row, meta) => {
  rv = ``

  if (1 == data) {
    rv = `Edited by Admin`
  }

  return rv
}

const renderTheIsDone = (data, type, row, meta) => {
  rv = ``

  if (1 == data) {
    rv = `Done`
  }

  return rv
}

const getEditTaskForm = (row) => {
  let rv = ``

  const [id, taskText, isDone] = [`id`, `taskText`, `isDone`].map((elem) => {
    val = row[elem]
    return val
  })

  const checkedAttr = 1 == isDone ? ` checked ` : ` `

  rv = `
  <form id="update-task-${id}" class="update-task">
    <input type="hidden" name="id" value="${id}">
    <div class="mb-3">
      <label for="taskText" class="form-label">Task text</label>
      <textarea class="form-control" id="taskText-${id}" rows="3" name="taskText">${taskText}</textarea>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="checkbox" value="" id="isDone-${id}" name="isDone" ${checkedAttr}>
      <label class="form-check-label" for="isDone-${id}">
        Task is done
      </label>
    </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          <input type="submit" class="btn btn-primary" value="Save changes">
        </div>
  </form>
    `

  return rv
}

const editTaskModal = (row) => {
  const id = row.id
  const editTaskForm = getEditTaskForm(row)
  const rv = `
  <div class="modal fade" id="updateTask-${id}" tabindex="-1" role="dialog" aria-labelledby="updateTaskModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="updateTaskModalLongTitle">Edit Task</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ${editTaskForm}
        </div>
      </div>
    </div>
  </div>
  `

  return rv
}

const editTaskButton = (data, type, row, meta) => {
  let rv = `<button type="button" class="btn btn-primary"
    data-toggle="modal" data-target="#updateTask-${data}">
  Edit
  </button>`
  rv += editTaskModal(row)

  return rv
}

const getErrMsgByResponse = (responseJSON) => {
  const errMsgs = JSON.parse(responseJSON.description)
  const errMsg = Array.isArray(errMsgs) ? errMsgs.join("\n") : errMsgs

  return errMsg
}

$(document).ready(() => {
  $("#tasks").DataTable({
    searching: false,
    lengthChange: false,
    processing: true,
    serverSide: true,
    pageLength: 3,
    order: [[0, "desc"]], // first field is 'id'
    ajax: {
      url: "api/",
      dataSrc: "data",
      dataFilter: (response) => {
        const json = jQuery.parseJSON(response)
        const message = json.message
        message.recordsFiltered = message.recordsTotal
        const rv = message

        return JSON.stringify(rv)
      },
    },
    columns: [
      ...[`id`, `name`, `email`, `taskText`].map((elem) => {
        const rv = { data: elem }
        return rv
      }),
      { data: `isDone`, render: renderTheIsDone },
      { data: `wasEdited`, render: renderTheWasEdited },
      { data: "id", render: editTaskButton },
    ],
    fnDrawCallback: function (oSettings) {
      $(".update-task").bind("submit", function (event) {
        event.preventDefault()

        const form = this
        const formValues = convertFormToJSON(form)

        $.ajax({
          type: "PUT",
          url: "api/",
          data: JSON.stringify(formValues),
          contentType: "application/json",
          dataType: "json",
        })
          .done(function () {
            onUpdateTaskDone()
          })
          .fail(function (jqXHR) {
            const httpStatus = jqXHR.status
            const responseJSON = jqXHR.responseJSON
            const errMsg = responseJSON.description
            onUpdateTaskFail(httpStatus, errMsg)
            return true
          })

        return true
      })
    },
  })

  $("form#add-new-task").bind("submit", function (event) {
    event.preventDefault()

    const form = this
    const formValues = convertFormToJSON(form)

    $.ajax({
      type: "POST",
      url: "api/",
      data: JSON.stringify(formValues),
      contentType: "application/json",
      dataType: "json",
    })
      .done(function (jqXHR) {
        const message = jqXHR.message

        onAddNewTaskDone(message)
      })
      .fail(function (jqXHR) {
        const responseJSON = jqXHR.responseJSON
        const errMsg = getErrMsgByResponse(responseJSON)
        alert("Failed to add task: \n" + errMsg)
      })

    return true
  })

  $("form#auth-login").bind("submit", function (event) {
    event.preventDefault()

    const form = this
    const formValues = convertFormToJSON(form)

    $.ajax({
      type: "POST",
      url: "api/user/login",
      data: JSON.stringify(formValues),
      contentType: "application/json",
      dataType: "json",
    })
      .done(function () {
        alert(`Login: done.`)
        $(form)
          .find(":input")
          .each((i, inputElem) => {
            inputElem.value = ``
          })
        $("div").modal("hide")
      })
      .fail(function () {
        alert("Failed to log in! Check name and/or passwd")
      })

    return true
  })

  $("#auth-logout").on("click", function (event) {
    event.preventDefault()

    $.ajax({
      type: "POST",
      url: "api/user/logout",
      data: ``,
      contentType: "application/json",
      dataType: "json",
    })
      .done(function () {
        alert(`Logout: done.`)
      })
      .fail(function () {
        alert("Failed to log out!")
      })

    return true
  })
})
