@extends('layouts.app')

@section('content')
    <div class="container">
        <audio id="audio" controls style="display: none" src="{{asset('audio/case-closed-531.mp3')}}"></audio>

        <div class="row ">
            <div class="col-md-3">
                <div class="users">
                    <h5>Users</h5>
                    <ul class="list-group list-chat-item">

                        @foreach($users as $user)
                            <li class="chat-user-list  @if($user->id==$friendInfo->id) active @endif">
                                <i class="fas fa-circle user-status-icon user-icon-{{$user->id}}"></i>
                                <a href="{{route('conversation',$user->id)}}">

                                    {{$user->name}}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="groups mt-5">
                    <h5>Groups <i class="fa fa-plus btn-add-group ml-3"></i></h5>
                    <ul class="list-group list-chat-item">

                        @foreach($groups as $group)
                            <li class="chat-group-list  ">

                                <a href="{{route('message-groups.show',$group->id)}}">

                                    {{$group->name}}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <div class="col-md-9">
              <div class="card mt-5">
                  <div class="card-header">
                      <div class="m-0 p-0">
                      <i class="fas fa-circle user-status-icon user-icon-{{$friendInfo->id}}" id="userStatusMead{{$friendInfo->id}}"></i>

                      <a href="">

                          {{$friendInfo->name}}
                      </a>
                  </div>
                      <p class="isOnline-{{$friendInfo->id}} m-0 p-0 ml-4  d-none text-success" id="isOnline">online</p>
                  </div>
                  <div class="card-body px-5" id="chatBody" style="overflow:scroll">
                      <div class="message-listing data-spy" id="messageWrapper" >


                      </div>
                      <p class="typing d-none" data-user-id="{{$friendInfo->id}}">Typing...</p>
                  </div>
              </div>
                <div class="chat-box">
                    <div class="chat-input bg-white mt-2" id="chatInput"  contenteditable="">

                    </div>

                    <div class="chat-input-toolbar">
                        <button title="Add file" class="btn  btn-sm btn-file-upload"><i class="fas fa-paperclip"></i></button>
                        <button title="Bold" class="btn  btn-sm tool-items" onclick="document.execCommand('bold',false,'');"><i class="fas fa-bold"></i></button>
                        <button title="Italic" class="btn  btn-sm tool-items" onclick="document.execCommand('italic',false,'');"><i class="fas fa-italic"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" role="dialog" id="add-group-modal">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('message-groups.store')}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Group name</label>
                            <input type="text" class="form-control" name="name">

                        </div>

                        <div class="form-group">
                            <label for="">Select members</label>
                            <select id="selectMembers" name="user_id[]" multiple="multiple" >
                                @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js" integrity="sha512-2ImtlRlf2VVmiGZsjm9bEyhjGW4dU7B6TNwh/hx/iSByxNENtj3WVE6o/9Lj4TJeVXPi4bnOIMXFIJJAeufa0A==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>

        $(function (){

            let $chatInput = $(".chat-input");
            let $chatInputToolbar = $(".chat-input-toolbar");
            let $chatBody = $(".chat-body");
            let $messageWrapper=$("#messageWrapper");

            let user_id="{{auth()->user()->id}}";
            let ip_address='127.0.0.1';
            let socket_port='8006';
            let socket=io(ip_address+':'+socket_port);
            let friendId="{{$friendInfo->id}}";
            socket.on('connect',function (){

                socket.emit('user_connected',user_id);

            });
            socket.on('updateUserStatus', (data)=>{
           //console.log("aaaaa"+data);
                var $userStatusIcon=$(".user-status-icon");
                   $("#isOnline").addClass('d-none');
                $userStatusIcon.removeClass('text-success');
               $userStatusIcon.attr('title','Away');

                $.each(data,function (key,val){
                    if(val!==null&&val !==0){
                        //   console.log(data);
                        let $userIcon=$(".user-icon-"+key);
                        var $isOnline=$(".isOnline-"+key);
                        $userIcon.addClass("text-success");
                        $isOnline.removeClass('d-none');
                        $userIcon.attr('title','online');
                    }
                })
            });

            let typingElement=$('.typing');
            socket.on('showTyping',data=>{
                console.log(data);
                if (data.typing){
                    console.log("remove")
                    typingElement.removeClass('d-none');

                }
                else {
                    console.log('add')
                    typingElement.addClass('d-none');

                }

            })
            $chatInput.keyup(function (e){
              e.pre
                if($(this).html()!=''){
                    console.log($(this).html());
                  let  typing=true;
                    socket.emit('typing', {user_id:typingElement.attr('data-user-id'), typing:true})
                }else {
                    socket.emit('typing', {user_id:typingElement.attr('data-user-id'), typing:false})

                }
               let message =$(this).html();
                // console.log(message)
               if(e.which===13 && !e.shiftKey){
                   $chatInput.html("");
                   sendMessage(message);
                   return  false;
               }
            });

            function sendMessage(message){
                let url ="{{route('message.send-message')}}";
                let form= $(this);
                let formData=new FormData();
                let token = "{{csrf_token()}}"

                formData.append('message',message);
                formData.append('_token',token);
                formData.append('receiver_id',friendId);
                appendMessageToSender(message);
                let timer = window.setInterval(function() {
                    let elem = document.getElementById('chatBody');
                    elem.scrollTop = elem.scrollHeight;
                    window.clearInterval(timer);
                }, 500);

                $.ajax({
                   url:url,
                   type:"POST",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                   data:formData,
                    processData:false,
                    contentType:false,
                    dataType:'json',
                   success:function (response){
                       if (response.success){
                           console.log('insert');
                       }else{
                           console.log("aa");
                       }
                   }
                });

            }
            function appendMessageToSender(message){
                let name='me';
                let userInfo='  <div class="col-md-12 user-info">\n' +
                    '<div class="chat-name font-weight-bold">\n' +
                      name +
                    '<span class="small time text-gray-500" title="'+ moment().format('MM/DD/YY h:m A')+'"> '+ moment().format('h:mm A')+'</span>\n' +
                    '</div>\n' +
                    '</div>';
                let messageContent='<div class="col-md-12 message-content">\n' +
                    '<div class="message-text">\n' +
                    message +
                    '</div>\n' +
                    '</div>';

                let newMessage='   <div class="row message align-item-center mb-2 ">\n' +
                    userInfo +
                    messageContent +
                    '</div>';
                $messageWrapper.append(newMessage);

            }
            function appendMessageToReceiver(message){
                typingElement.addClass('d-none');
                let name='{{$friendInfo->name}}';
                let userInfo='  <div class="col-md-12 user-info">\n' +
                    '<div class="chat-name font-weight-bold">\n' +
                    name +
                    '<span class="small time text-gray-500" title="'+ moment(message.created_at,'YYYY-MM-DD HH:mm:ss').format('MM/DD/YY h:m A')+'"> '+ moment(message.created_at,'YYYY-MM-DD HH:mm:ss').format('h:mm A')+'</span>\n' +
                    '</div>\n' +
                    '</div>';
                let messageContent='<div class="col-md-12 message-content">\n' +
                    '<div class="message-text">\n' +
                    message.content +
                    '</div>\n' +
                    '</div>';

                let newMessage='   <div class="row message align-item-center mb-2 ">\n' +
                    userInfo +
                    messageContent +
                    '</div>';
                $messageWrapper.append(newMessage);

            }

            socket.on("private-channel:App\\Events\\PrivateMessageEvent",function (message){
                var timer = window.setInterval(function() {
                    var elem = document.getElementById('chatBody');
                    elem.scrollTop = elem.scrollHeight;
                    window.clearInterval(timer);
                }, 500);
                appendMessageToReceiver(message);
                document.getElementById('audio').play();
            });

        });
        $("#selectMembers").select2();
        $addGroupModal=$("#add-group-modal");
        $(document).on('click','.btn-add-group',function (){

            $addGroupModal.modal('show');
        });

    </script>

@stop
