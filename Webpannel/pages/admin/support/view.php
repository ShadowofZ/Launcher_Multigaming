<?php
/**
 * Created by PhpStorm.
 * User: hubert_i
 * Date: 14/06/16
 * Time: 20:12
 */

$id = $match['params']['id'];

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="launcherPanel">
    <meta name="author" content="Leo HUBERT">

    <link rel="shortcut icon" href="/assets/images/favicon_1.ico">

    <title><?php echo $site;?> panel</title>

    <link href="/assets/plugins/jquery-circliful/css/jquery.circliful.css" rel="stylesheet" type="text/css" />

    <link href="/assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/core.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/components.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/icons.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/pages.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/menu.css" rel="stylesheet" type="text/css" />
    <link href="/assets/css/responsive.css" rel="stylesheet" type="text/css" />

    <link href="/assets/plugins/sweetalert/dist/sweetalert.css" rel="stylesheet" type="text/css">
    <link href="/assets/plugins/jquery-circliful/css/jquery.circliful.css" rel="stylesheet" type="text/css" />


    <!-- HTML5 Shiv and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->

    <script src="/assets/js/modernizr.min.js"></script>

</head>


<body>

    <?php include "jointures/header_admin.php"?>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script>
        var init = null;
        $(function() {
            var total_messages = null;

            afficheConversation();
            function afficheConversation()
            {
                $.post(
                    '/api/support/admin/get',
                    {
                        token : "<?php echo $_SESSION['token'];?>",
                        id : "<?php echo $id;?>"
                    },

                    function(data){
                        var obj = JSON.parse(data);
                        var chat = document.getElementById("chat");
                        chat.innerHTML = "";
                        if (obj.status == 42)
                        {
                            var total = obj.total;
                            var i = 0;

                            if (total_messages != total)
                            {
                                if (total_messages != null)
                                {
                                    var audio = new Audio('/assets/sounds/job-done.mp3');
                                    audio.play();
                                    $.Notification.notify('success','top right','New message', "You have a new message");
                                }
                                total_messages = total;
                            }
                            while (total > 0)
                            {
                                var date = new Date(obj.messages[i].send_at);
                                if (obj.messages[i].me == 1)
                                    chat.innerHTML += '<li class="clearfix odd"> <div class="chat-avatar"> <img src="'+ obj.messages[i].sender_picture +'"> <i>'+ date.getHours() + ":" + date.getMinutes() +'</i> </div> <div class="conversation-text"> <div class="ctext-wrap"><i>'+ obj.messages[i].sender_name +'</i> <p>'+ obj.messages[i].message +'</p> </div> </div> </li>';
                                else
                                    chat.innerHTML += '<li class="clearfix"> <div class="chat-avatar"> <img src="'+ obj.messages[i].sender_picture +'"> <i>'+ date.getHours() + ":" + date.getMinutes() +'</i> </div> <div class="conversation-text"> <div class="ctext-wrap"><i>'+ obj.messages[i].sender_name +'</i> <p>'+ obj.messages[i].message +'</p> </div> </div> </li>';
                                i++;
                                total--;
                            }
                            chat.scrollTop = 8000;
                            if (init == null)
                            {
                                document.getElementById("support_title").value = obj.support_title;
                                document.getElementById("support_status" + obj.support_status).selected = true;
                                init = 1;
                            }
                        }
                        else if (obj.status == 41)
                            window.location="/logout";
                        else if (obj.status == 44)
                            sweetAlert("Missing permission", obj.message, "error");
                        else if (obj.status ==  40)
                            sweetAlert("Little 4:04 error ...", "This conversation doesn't exits", "error");
                        else
                            $.Notification.notify('error','bottom center','Internal Error', "Error: " + obj.status + " | " + obj.message);
                    },

                    'text'
                );
            }
            setInterval(afficheConversation, 4000);
        });
        function sendMessage()
        {
            $.post(
                '/api/support/admin/send',
                {
                    token : "<?php echo $_SESSION['token'];?>",
                    support_id : "<?php echo $id;?>",
                    message : document.getElementById("chat_message").value
                },

                function(data){
                    var obj = JSON.parse(data);
                    var chat = document.getElementById("chat");
                    if (obj.status == 41)
                        window.location="/logout";
                    else if (obj.status == 44)
                        sweetAlert("Missing permission", obj.message, "error");
                    else if (obj.status != 42)
                        $.Notification.notify('error','bottom center','Internal Error', "Error: " + obj.status + " | " + obj.message);
                },
                'text'
            );
        }
        function assign_support(support_id) {
            $.post(
                '/api/support/admin/assign',
                {
                    token : "<?php echo $_SESSION['token'];?>",
                    id : support_id
                },

                function(data){
                    var obj = JSON.parse(data);

                    if (obj.status == 42)
                    {
                        $.Notification.notify('success','top right','Assigned !', obj.message);
                    }
                    else if (obj.status == 41)
                        window.location="/logout";
                    else if (obj.status == 44)
                        sweetAlert("Missing permission", obj.message, "error");
                    else
                        swal("Error...", obj.message, "error");
                },

                'text'
            );
        }
        function save_support(support_id)
        {
            init = null;
            $.post(
                '/api/support/admin/save',
                {
                    token : "<?php echo $_SESSION['token'];?>",
                    support_id : "<?php echo $id;?>",
                    status : document.getElementById("support_status").value,
                    title: document.getElementById("support_title").value
                },

                function(data){
                    var obj = JSON.parse(data);

                    if (obj.status == 42)
                        $.Notification.notify('success','top right','Saved !', obj.message);
                    else if (obj.status == 41)
                        window.location="/logout";
                    else if (obj.status == 44)
                        sweetAlert("Missing permission", obj.message, "error");
                    else
                        swal("Error...", obj.message, "error");
                },

                'text'
            );
        }
    </script>





<div class="wrapper">
    <div class="container">

        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Support view</h4>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card-box">
                <h4 class="m-t-0 m-b-20 header-title"><b>Chat</b></h4>

                <div class="chat-conversation">
                    <ul id="chat" class="conversation-list nicescroll" tabindex="5002">

                    </ul>
                    <div class="row">
                        <div class="col-sm-9 chat-inputbar">
                            <input id="chat_message" type="text" class="form-control chat-input" placeholder="Enter your text">
                        </div>
                        <div class="col-sm-3 chat-send">
                            <button type="submit" class="btn btn-md btn-primary btn-block waves-effect waves-light">Send</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="col-lg-5">
            <div class="panel panel-color panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Support control</h3>
                </div>
                <div class="panel-body">
                    <p>
                        <input id="support_title" type="text" name="state-success" class="form-control" placeholder="Support title ...">
                        <br>
                        <select id="support_status" class="form-control">
                            <option selected id="support_status0" value="0">Not assigned</option>
                            <option id="support_status1" value="1">Assigned</option>
                            <option id="support_status2" value="2">In progress</option>
                            <option id="support_status3" value="3">Done</option>
                            <option id="support_status4" value="4">Close</option>
                        </select>
                    </p>
                </div>
                <div class="panel-footer">
                    <button type="button" class="btn btn-success btn-custom waves-effect w-md waves-light m-b-5" onclick="assign_support(<?php echo $id;?>)">Assign to me</button>
                    <button type="button" class="btn btn-primary btn-custom waves-effect w-md waves-light m-b-5" onclick="save_support(<?php echo $id;?>)">Save</button>
                </div>
            </div>
        </div>

        <?php include "jointures/footer.php";?>

    </div> <!-- end container -->
</div>
<!-- End wrapper -->


<!-- jQuery  -->
<script src="/assets/js/jquery.min.js"></script>
<script src="/assets/js/bootstrap.min.js"></script>
<script src="/assets/js/detect.js"></script>
<script src="/assets/js/fastclick.js"></script>
<script src="/assets/js/jquery.blockUI.js"></script>
<script src="/assets/js/waves.js"></script>
<script src="/assets/js/wow.min.js"></script>
<script src="/assets/js/jquery.nicescroll.js"></script>
<script src="/assets/js/jquery.scrollTo.min.js"></script>

<!-- Moment  -->
<script src="/assets/plugins/moment/moment.js"></script>

<!-- Sweet Alert  -->
<script src="/assets/plugins/sweetalert/dist/sweetalert.min.js"></script>

<!-- skycons -->
<script src="/assets/plugins/skyicons/skycons.min.js" type="text/javascript"></script>

<!-- Todojs  -->
<script src="/assets/pages/jquery.todo.js"></script>

<!-- chatjs  -->
<script src="/assets/pages/jquery.chat.js"></script>

<!-- Notifications -->
<script src="/assets/plugins/notifyjs/dist/notify.min.js"></script>
<script src="/assets/plugins/notifications/notify-metro.js"></script>

<script src="/assets/js/jquery.core.js"></script>
<script src="/assets/js/jquery.app.js"></script>

</body>
</html>
