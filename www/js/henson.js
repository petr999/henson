const convertAddNewTaskToJSON = form => {
  let values = {}

  $( "form#add-new-task :input" ).each( ( i, inputElem ) => {
    const [ name, value ] = [ inputElem.name, inputElem.value ]
    values[ name ] = value
  } )

  return values
}

const onAddNewTaskDone = () => {

  $('#tasks').DataTable()
     .rows().invalidate('data')
     .draw(false)

  $( "form#add-new-task :input" ).each( ( i, inputElem ) => {
    inputElem.value = ``
  } )

}

$(document).ready( () => {
  $('#tasks').DataTable({
    "searching": false,
    "lengthChange": false,
    "processing": true,
    "serverSide": true,
    "pageLength": 3,
    "order": [ [ 0, "desc", ],], // first field is 'id'
    "ajax": { "url": "/api/",
      "dataSrc": "data",
      "dataFilter": response => {
        const json = jQuery.parseJSON( response )
        const message = json.message
        message.recordsFiltered = message.recordsTotal
        const rv = message

        return JSON.stringify( rv )
      },
    },
    columns: [ `id`, `name`, `email`, `taskText`, `isDone` ].map( elem => {
      const rv = { data: elem, }
      return rv;
    }  ),
  })

  $('form#add-new-task').bind('submit', function( event ){
      event.preventDefault()

      const form = this
      const formValues = convertAddNewTaskToJSON( form )

      $.ajax({
          type: "POST",
          url: "/api/",
          data: JSON.stringify( formValues ),
          contentType: "application/json",
          dataType: "json"
      }).done(function() {

        onAddNewTaskDone()

      }).fail(function() {
          alert("Failed to add task")
      })

      return true
  })

} )

