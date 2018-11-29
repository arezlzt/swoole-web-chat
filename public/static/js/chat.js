"use strict";
window.chat = {
    debug: true,
    connect: null,
    uid: 0,
    to_uid: 0,
    myMessageRunTwinkleInterval: null,
    url: "ws://45.40.253.161:9501",
    messageCountStatus: 0,
    messageContent:'',
    searchContent:'',
    init: function (uid, messageContent, searchContent) {
        this.uid = uid;
        this.messageContent = messageContent;
        this.searchContent = searchContent;
        this.initWebSocket();
        this.initClickEvent();
    },
    initWebSocket: function () {
        var $this = this;
        $this.connect = new WebSocket(this.url);//连接服务器
        $this.connect.onopen = function (event) {
            chat.log(event);
            chat.log('连接了');
            var message = {
                type: 0,
                message: 'user ' + $this.uid + ' online',
                uid: $this.uid
            };
            $this.sendMessage(message);
        };
        $this.connect.onmessage = function (event) {
            $this.log(event.data);
            var data = JSON.parse(event.data);
            switch (data.type) {
                case 1:
                    $('.dialog-message-icon').find('sup').html(data.data.count);
                    $this.myMessageRunTwinkleInterval = setInterval('chat.myMessageRunTwinkle()', 500);
                    break;
                case 2:
                    $('.dialog-content').find('ul').append( data.data.html);
                    $('.dialog-content').scrollTop($('.dialog-content')[0].scrollHeight);
                    $('.dialog-message').find('textarea').val('');
                    break;
            }

        };
        $this.connect.onclose = function (event) {
            chat.log("已经与服务器断开连接\r\n当前连接状态：" + this.readyState);
        };
        $this.connect.onerror = function (event) {
            chat.log("WebSocket异常！");
        };
    },
    log: function (message) {
        if (this.debug) {
            console.log(message);
        }
    },
    sendMessage: function (data) {
        this.connect.send(JSON.stringify(data));
    },
    myMessageRunTwinkle: function () {
        if (this.messageCountStatus == 0) {
            $('.dialog-message-icon').removeClass('fa-bell-o').addClass('fa-bell');
            this.messageCountStatus = 1;
        } else {
            $('.dialog-message-icon').removeClass('fa-bell').addClass('fa-bell-o');
            this.messageCountStatus = 0;
        }
    },
    sendChatMessage:function() {
        var message = $('.dialog-message').find('textarea').val().toString().replace(/\n/g, '<br>');
        if (message.toString().trim() == '') {
            return false;
        }
        var data = {
            message:message,
            type:3,
            from_uid:this.uid,
            to_uid:this.to_uid
        }
       this.sendMessage(data);
    },
    runFriendTwinkle:function() {

    },
    initClickEvent: function () {
        var $this = this;
        /*添加好友弹框*/
        $('.fa-user-plus').on('click', function () {
            layer.open({
                'title': '添加好友',
                type: 1,
                skin: 'layui-layer-rim',
                area: ['820px', '490px'],
                content: $this.searchContent
            });
        });
        /*消息弹框*/
        $('.dialog-message-icon').on('click', function () {
            if (null != $this.myMessageRunTwinkleInterval) {
                clearInterval($this.myMessageRunTwinkleInterval);
            }
            layer.open({
                'title': '我的消息',
                type: 1,
                skin: 'layui-layer-rim',
                area: ['820px', '490px'],
                content: $this.messageContent
            });
            $.post('getMessage', {uid: $this.uid}, function (data) {
                $('#message-content-body').html(data.data);
            });
        });
        /*打开聊天窗口*/
        $('.box-content [prop="tab_user"]').on('click', 'ul li', function () {
            $this.to_uid = $(this).data('id');
            $.post('getChatRecord', {to_uid: $this.to_uid, from_uid: $this.uid, page: 1}, function (data) {
                if (data.status) {
                    $('.chat-dialog').removeClass('hide');
                    if (data.data.more == 0) {
                        $('.dialog-content p').hide();
                    } else {
                        $('.dialog-content p').attr('data-id', $this.to_uid);
                        $('.dialog-content p').attr('data-page', 1);
                    }
                    $('.dialog-content ul').prepend(data.data.html);
                    $('.dialog-content').scrollTop($('.dialog-content')[0].scrollHeight);
                }
            });
        });
        $('.dialog-content p').on('click', function () {
            var toUid = $(this).data('id');
            var page = parseInt($(this).attr('data-page')) + 1;
            $.post("getChatRecord", {to_uid: toUid, from_uid: chat.uid, page: page}, function (data) {
                if (data.status == 1) {
                    if (data.data.lastPage == page) {
                        $('.dialog-content p').hide();
                    } else {
                        $('.dialog-content p').attr('data-page', page);
                    }
                    $('.dialog-content ul').prepend(data.data.html);
                }
            });
        });
        $('.min-content').click(function () {
            $('.chat-min,.chat-box').toggleClass('hide');
        });
        //关闭聊天主面板
        $('.chat-box').on('click', '.close', function () {
            $('.chat-min,.chat-box').toggleClass('hide');
        });
        //发送聊天信息  ctrl+enter 换行  enter发送信息
        $('.chat-dialog').on('keypress', '.dialog-message textarea', function (event) {
            if (event.ctrlKey && event.keyCode == 10) {
                $(this).val($(this).val() + '\n');
            }
            else if (event.keyCode == 13) {
                $this.sendChatMessage();
                return false;
            }
        });
        //发送按钮 发送信息
        $('.chat-dialog').on('click', '[btn="send"]', function () {
            $this.sendChatMessage();
        });
        //关闭聊天对话框面板
        $('.chat-dialog').on('click', '[btn="close"]', function () {
            $('.chat-dialog').toggleClass('hide');
        });
        //tab切换
        $('.box-tab').on('click', 'div', function () {
            if (!$(this).hasClass('active')) {
                $(this).addClass('active').siblings().removeClass('active');
                var prop = $(this).attr('prop');
                $('.box-content').find('[prop="' + prop + '"]').removeClass('hide').addClass('active')
                    .siblings().addClass('hide').removeClass('active');
            }
        });

        //退出登录
        $('[btn="logout"]').on('click', function () {
            Dialog.confirm('确定退出?', function () {
                // window.top.location.href = '{{route('user_logout')}}';
            })
        });
    }
}

function search() {
    var search = $('#search-nickname').val();
    $.post("search", {uid: chat.uid, search: search}, function (data) {
        $('#search-content').html(data.data);
    });
}
function postMessage(dom){
    var $this = $(dom);
    var to_uid = $this.data('uid');
    $.post("addMessage", {to_uid:to_uid, from_uid:chat.uid}, function (data) {
        alert(data.message);
        if(data.status == 1 || data.status == -1) {
            $this.parent('div').append('<span class="label label-info">待回应</span>');
            $this.remove();
        }
    });
}
function passFriendRequest(document) {
    var $this = $(document);
    var msg_id = $this.data('id');
    $.post("passFriendRequest", {msg_id:msg_id}, function(data) {
        alert(data.message);
        if(data.status == 1) {
            $this.parent('td').append('<span class="label label-primary pull-right">已添加</span>');
            $this.remove();
        }
    });
}
