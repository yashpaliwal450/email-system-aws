<html>
  <head>
    <title>Email Syatem</title>
    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T"
          crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <style>
        .clickable-row {
            cursor: pointer;
        }
        .update {
          cursor: pointer;
        }
        .btn {
          border: none;
          outline: none;
          padding: 10px 16px;
          background-color: #f1f1f1;
          cursor: pointer;
        }
        .active, .btn:hover {
          background-color: #666;
          color: white;
        }
    </style>
    <script>
      $().ready(function () {
        $.ajax({
          url:"http://3.26.48.251:8000/api/dashboardData",
          type: "GET",
          headers:{'Authorization': localStorage.getItem('user_token')},
          success:function(data){
            if(data.success == true){
              if(data.image!=null){
                  $("#profileimg").append("<img alt='Logo' src='"+data.image+"' id ='userPhoto' class='rounded-circle' width='50' height='50'>");
                }
                else{
                  var name = data.user.first_name.substring(0, 1)+data.user.last_name.substring(0,1)
                  $("#userName").html(name);
                }
                $.each(data.emails.data, function (key, value) {
                  $("#tblMail").append("<tr class='clickable-row' data-href=http://3.26.48.251:8000/api/mail_view?id="+value.email_id+"><td> By:"+value.email+"</td><td>" + value.body  +"</td><td>" +value.subject  +"</td><td>" +value.created_at  +"</td></tr>");
                  
                });
                $('.clickable-row').on('click', function() {
                      window.location.href= $(this).attr('data-href');
                  });
                  
            }
            },error: function(error) {
                
                localStorage.removeItem('user_token');
                window.location.href="http://3.26.48.251:8000/api/home";
              }
        });

        $('#logout').on('click',function(e) {
          $.ajax({
            url:"http://3.26.48.251:8000/api/logout",
            headers: {'Authorization': localStorage.getItem('user_token')},
            success:function(response){
              if(response.success == true){
                localStorage.removeItem('user_token');
                window.location.href="http://3.26.48.251:8000/api/home";
              }
              else{
                console.log(response);
              }
            }
            ,error: function(error) {
                console.log(error); 
              }
          });
        });

        $('#update').on('click',function(){
          window.location.href="http://3.26.48.251:8000/api/updateUserFormView";
        });

          $('#submitBtn').on('click',function(e) {
            var formData = new FormData($('#emailForm')[0]);
            $.ajax({
              
              url: "http://3.26.48.251:8000/api/send_mail", 
              type: "POST",
              data: formData,
              contentType: false,
              processData: false,
              headers: {'Authorization': localStorage.getItem('user_token')},
              success: function(response) {
                if(response==1){
                  $('#mymodal').modal('hide');
                  $("#response").html("mail sent successfully");
                  setTimeout(function() {
                            $("#response").fadeOut();
                        }, 3000); 
                }
                if(response.error==true){
                  $('#mymodal').modal('hide');
                $("#response").html("Wrong Email Id");
                setTimeout(function() {
                          $("#response").fadeOut();
                      }, 3000);
                }
              },
              error: function(error) {
                $('#mymodal').modal('hide');
                $("#response").html("Wrong Email Id");
                setTimeout(function() {
                          $("#response").fadeOut();
                      }, 3000);
                console.log(error); // Log the error for testing purposes
                // You can show error messages or update the UI here
              }
            });
          });
          $('#inbox').on('click',function(e) {
            window.location.href="http://3.26.48.251:8000/api/inbox";
          });
          $('#sent_mail').on('click',function(e) {
            window.location.href="http://3.26.48.251:8000/api/sent_mail_page";
          });
      });
    </script>


  </head>

<body>

  <header>
      <nav class="navbar navbar-expand-md navbar-dark "
          style="background-color: #ff9933">
          <div class="collapse navbar-collapse" id="navbarNav">
          <div>
              <a href="http://3.26.48.251:8000/api/inbox" class="navbar-brand">Email System</a>
          </div>
            <div class="ml-auto ">
                <a id="update" class="navbar-brand rounded-circle">
                    <div id="profileimg">
                    </div>
                    <div class="rounded-circle" id="userName">
                      
                    </div>
                  
                </a>
                <a id="logout" href="" class="navbar-brand">logout</a>
            </div>
          </div>
      </nav> 
  </header>
  <br>
  <div class="container-fluid">
    <div class="row">
      <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
        <div class="position-sticky">
          
            <ul class="nav flex-column">
              <li class="nav-item">
                <button class="nav-link" data-toggle="modal" data-target="#mymodal">compose</button><br>
              </li>
              <li class="nav-item">  
                <button class="nav-link active" id="inbox">Inbox</button><br>
              </li>
              <li class="nav-item">  
                <button class="nav-link " id="sent_mail">Sent Mail</button>
              </li>
            </ul>
        
        </div>
      </nav>
              

      <div class="modal" id="mymodal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h2> mail </h2>
              <button class="close" type="button" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
            
                  <form name='emailForm' id="emailForm"  enctype="multipart/form-data">
                    @csrf    
                    <div style="text-align:center;">
                      <table>
                          <div class = "form-group">
                            <tr><td>
                            <label for="reciver_mail">sent to:</label>
                            </td><td>
                                    <input type="email" name="reciver_mail" id="reciver_mail"  > 
                            </td></tr>
                          </div>
                          
                          <div class = "form-group">
                            <tr><td>
                            <label for="cc">cc: </label>
                            </td><td>
                            <input type="email" name="cc" id="cc" >
                            </td></tr>
                          </div>
                          <div class = "form-group">
                            <tr><td>
                            <label for="bcc">bcc: </label>
                            </td><td>
                            <input type="email" name="bcc" id="bcc" >
                            </td></tr>
                          </div>
                          <div class = "form-group">
                            <tr><td>
                            <label for="subject">subject:</label>
                            </td><td>
                            <input type="text" name="subject" id="subject" >
                            </td></tr>
                          </div>

                          <div class = "form-group">
                            <tr><td>
                            <label for="body">Body:</label>
                            </td><td>
                            <textarea name="body" id="body" cols="40"></textarea>
                            </td></tr>
                          </div>
                          <div class = "form-group">
                            <tr><td>
                                <label for="attachments">Attachments:</label>
                                </td><td>
                            <input type="file" name="attachments[]" id="attachments" multiple>
                            </td></tr>
                          </div>
                          <tr><td>
                          
                              <button type="button" id ="submitBtn" class="btn btn-primary">send</button>&nbsp;&nbsp;&nbsp;&nbsp;</td><td>
                              
                          </td></tr>
                      </table>
                    </div>
                  </form>

                </div>
              </div>
            </div>
          </div>
      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <div id="response"></div>
              <h3 class="text-center">Inbox</h3>
              <hr>
              <br>
              <table class="table" >
                  <tbody id="tblMail">
                  </tbody>
              </table>
          <div class="mt-5" id="paginate">
          </div>
      </main>
    </div>
  </div>
  <script>
        const rows = document.querySelectorAll('.clickable-row');
        rows.forEach(row => {
            row.addEventListener('click', () => {
                window.location = row.getAttribute('data-href');
            });
        });
    </script>


</script>

</body>
</html>

