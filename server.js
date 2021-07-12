var app = require('express')();
var http = require('http').Server(app);
const { instrument } = require("@socket.io/admin-ui");
var io = require('socket.io')(http , {
        cors: {
            origin: '*',
        }
});
var Redis=require('ioredis');
var redis=new Redis();
var users=[];
var groups=[];
instrument(io, {
    auth: false
});

http.listen(8006,function (){
    console.log('Listening to port 8006');
});
redis.subscribe('private-channel',function (){
    console.log('subscribed to private channel');
});
redis.subscribe('group-channel',function (){
    console.log('subscribed to group channel');
});

redis.on('message',function (channel,message){
    message=JSON.parse(message);
     if (channel==='private-channel'){
         console.log("aaa");
         let data = message.data.data;
         let receiver_id=data.receiver_id;
         let event=message.event;
         io.to(users[receiver_id]).emit(channel+":"+event,message.data.data);
     }
     if (channel==='group-channel'){
         let data=message.data.data;
         if (data.type==2){
             let socket_id=getSocketIdOfUserInGroup(data.sender_id,data.group_id);
             console.log(socket_id);
             let socket=io.sockets.sockets.get(socket_id);

             socket.to('group'+data.group_id).emit('groupMessage',message.data.data);
         }
     }
   // console.log(message);
});
io.on('connection',function (socket){

    socket.on("user_connected",function (user_id){
        users[user_id]=socket.id;
        console.log(users);
          io.emit('updateUserStatus',users);
        console.log("User:"+user_id+" connected");

    });

    socket.on("disconnect",function (){
       var i=users.indexOf(socket.id);
       users.splice(i,1,0);
       io.emit("updateUserStatus",users);
       console.log(users);
    });

    socket.on('join_groups',function (data){
        console.log("in join")
        data['socket_id']=socket.id;
        if(groups[data.group_id]){
            console.log("exist")
          var userExist=checkIfUserExistInGroup(data.user_id,data.group_id);

            if (!userExist){
                groups[data.group_id].push(data);
                socket.join(data.room);
                console.log('---')
                console.log(groups);
            }else {
                if (getSocketIdOfUserInGroup(data.user_id,data.group_id)!=socket.id){
                     changeSocketIdFromUser(data.user_id,data.group_id,socket.id);
                    socket.join('group'+data.group_id);

                }
            }
        }else {
            groups[data.group_id] = [data];
            socket.join(data.room);

        }
        console.log(`socket_id : ${socket.id} and user_id :${data.user_id}`);
        console.log(groups);

    });

    socket.on('typing',(data)=>{

        let socket_id=returnSocketIdFromUsers(data.user_id);
        console.log(socket_id);
        if (data.typing){
            console.log('yes')
            console.log(io.to(socket_id).emit('showTyping',{typing:true}));
            // io.to(socket_id).emit('showTyping',{typing:true});
        }else {
            console.log('no')
            io.to(socket_id).emit('showTyping', {typing: false});
        }
        // console.log( io.sockets);
    });


});

function  checkIfUserExistInGroup(user_id,group_id){
       var group=groups[group_id];
       var exist=false;
    console.log(groups.length);
     if (groups.length>0){
         for (var i=0;i<group.length;++i){
             if (group[i]['user_id']==user_id){
                 exist=true;
                 break;
             }
         }
     }
       return exist;
}

function getSocketIdOfUserInGroup(user_id,group_id) {
    var group = groups[group_id];
    // console.log('aaaa');
    // console.log(group);
    if (groups.length > 0) {
        for (var i = 0; i < group.length; ++i) {
            if (group[i]['user_id'] == user_id) {
                console.log('ssss')
                console.log(group[i]['socket_id']);
                return group[i]['socket_id'];
            }
        }
    }
}
function returnSocketIdFromUsers(user_id){
    let socket_id=users[user_id];
    if (socket_id)
        return socket_id;

    return null;
}


function  changeSocketIdFromUser(user_id,group_id,new_socket){
    var group=groups[group_id];
    // console.log('aaaa');
    // console.log(group);
    if (groups.length>0){
        for (var i=0;i<group.length;++i){
            if (group[i]['user_id']==user_id){
                 group[i]['socket_id']=new_socket;
            }
        }
        console.log(groups);
    }

}

