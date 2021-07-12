@extends('layouts.app')

@section('content')
    <div class="container">
<div class="row ">
    <div class="col-md-3">
        <div class="users">
            <h5>Users</h5>
            <ul class="list-group list-chat-item">
                @foreach($users as $user)
                <li class="chat-user-list">
                    <i class="fas fa-circle user-status-icon user-icon-{{$user->id}}"></i>
                    <a href="{{route('conversation',$user->id)}}">

                         {{$user->name}}

                    </a>

                </li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-md-9">
        <h1 class="text-center bg-blue">Message Section</h1>
        <h5 class="alert-danger m-2 p-3 text-center">Select user from the list to begin conversation</h5>

    </div>
</div>
    </div>
@endsection


@section('scripts')
    <script>

        $(function (){
          let user_id="{{auth()->user()->id}}";
          let ip_address='127.0.0.1';
          let socket_port='8006';
          let socket=io(ip_address+':'+socket_port);
          socket.on('connect',function (){

          socket.emit('user_connected',user_id);

          });
            socket.on('updateUserStatus', (data)=>{

                var $userStatusIcon=$(".user-status-icon");
                $userStatusIcon.removeClass('text-success')
                $userStatusIcon.attr('title','Away');

             $.each(data,function (key,val){
                 if(val!==null&&val !==0){
                  //   console.log(data);
                     let $userIcon=$(".user-icon-"+key);
                     $userIcon.addClass("text-success");
                     $userIcon.attr('title','online');
                 }
             })
            });

        });
    </script>

    @stop
