$(document).ready(function () {
    function resetInfo(){
        $("#username-cmt").val("");
        $("#content-cmt").val("");
    }
    $("#commentForm").submit(function(e){
        e.preventDefault();
        var comment_author =$("#username-cmt").val();
        var comment_content = $("#content-cmt").val();
        var comment_post_ID =  $("#post_id").val();
        var comment_parent =$("#comment_parent").val(); 
        $.ajax({
            type: "post",
            url: cmt_obj.ajax_url,
            data: {
                "action": "comment_submit",
                "cmt_nonce" : cmt_obj.cmt_nonce,
                "comment_author": comment_author,
                "comment_content":comment_content,
                "comment_post_ID":comment_post_ID,
                "comment_parent_id": comment_parent,
            },
            success: function (response) {
                console.log(response);
                if(response.success){
                    if(response.data.comment_parent_id=="0"){
                        $(".no-comment").remove();
                        $(".reply-box").prepend(response.data.comment_html);
                        resetInfo();
                    }
                    
                }
            },
            error: function(){
                console.log("lỗi");
            }
        });
    });

    // hiển thị form khi reply
    showFormReply();
    function showFormReply(){
        $(document).on("click",".btn-reply",function(){
            var comment_id = $(this).attr("data-commentid");
            $(`#reply-form-${comment_id}`).css("display","block")
        })
    }

    // Xử lý ajax trả lời bình luận
    $(document).on("submit", ".formReply", function(event) {
        event.preventDefault();
        var form = $(this);
        var commentId = $(this).attr("data-commentId");
        var comment_author =$(`#user_${commentId}`).val();
        var comment_content = $(`#content_${commentId}`).val();
        var comment_post_ID =  $(`#post_id_${commentId}`).val();
        var comment_parent =$(`#comment_parent_${commentId}`).val(); 
        if (!comment_author || !comment_content) {
            alert("Vui lòng điền đầy đủ thông tin.");
            return;
        }
        $.ajax({
            type: "post",
            url: cmt_obj.ajax_url,
            data: {
                "action": "comment_submit",
                "cmt_nonce" : cmt_obj.cmt_nonce,
                "comment_author": comment_author,
                "comment_content":comment_content,
                "comment_post_ID":comment_post_ID,
                "comment_parent_id": comment_parent,
            },
            success: function (response) {
                
                console.log(response.data.comment_html);
                if(response.success){
                    var replyListId = '#reply-list-' + commentId;
                    form.closest('#create-form-reply').find(".reply-form").css("display",'none');
                    form.find('input[name="comment_author"]').val("");
                    form.find('input[name="comment_content"]').val("");
                    var replyBox = form.closest('#create-form-reply').siblings(".reply-reply-box");
                    if (!$(replyListId).length) {
                        $('<div>', {
                            id: 'reply-list-' + commentId,
                            class: 'reply-reply-list'
                        }).appendTo(replyBox); // Chèn div vào comment-item
                    }

                    $(replyListId).append(response.data.comment_html);
                }
            },
            error: function(){
                console.log("lỗi rồi");
            }
        });
    });

    // Kiểm tra và cập nhật trạng thái thích khi tải trang
    $(".like-count").each(function() {
        var $likeCountElement = $(this);
        var commentId = $likeCountElement.attr("data-commentId");

        // Kiểm tra localStorage để xác định trạng thái đã thích
        if (localStorage.getItem(`liked_${commentId}`) === 'true') {
            // Nếu đã thích rồi, thêm lớp 'liked' vào SVG
            $likeCountElement.attr("data-liked", "true");
            $likeCountElement.find(".svg-like").addClass("liked");
        }
    });
    //Xử lý nút like bình luận
    $(document).on("click",".like-count",function(){
        var $likeCountElement = $(this);
        var commentId = $(this).attr("data-commentId");
        if (localStorage.getItem(`liked_${commentId}`) === 'true') {
            return;
        }
        $.ajax({
            type: "post",
            url: cmt_obj.ajax_url,
            data: {
                "action": "like_comment",
                "cmt_nonce" : cmt_obj.cmt_nonce,
                "comment_id": commentId,
            },
            success: function (res) {
                console.log(res);
                if(res.success){
                    $likeCountElement.attr("data-liked", "true");
                    $likeCountElement.find(".svg-like").addClass("liked");
                    $(`#countLike-${commentId}`).html(`(${res.data.like_count})`);
                    localStorage.setItem(`liked_${commentId}`, 'true');
                }
            },
            error: function(){
                console.log("Lỗi");
            }
        });
    })

    //Load more reply
    $(document).on("click",".reply-more",function(){
        var $btnloadMore = $(this);
        var commentId = $btnloadMore.attr("data-commentId");
        var nextPage = $btnloadMore.attr("data-page");
        $.ajax({
            type: "post",
            url: cmt_obj.ajax_url,
            data: {
                action:"load_more_comments_child",
                "cmt_nonce" : cmt_obj.cmt_nonce,
                "comment_id": commentId,
                "paged": nextPage
            },
            success: function (response) {
                console.log(response);
                if(response.success){
                    var replyListId = '#reply-list-' + commentId;
                    $(replyListId).append(response.data.comments_html);
                    $btnloadMore.replaceWith(response.data.pagination_html);
                }
            },error:function(){
                console.log("Lỗi");
            }
        });
    })
    $(document).on("click",".btn-more",function(){
        var $btnMoreP = $(this);
        var nextPage = $btnMoreP.attr("data-page");
        $.ajax({
            type: "post",
            url: cmt_obj.ajax_url,
            data: {
                action:"load_more_comments_parent",
                "cmt_nonce" : cmt_obj.cmt_nonce,
                // "comment_id": commentId,
                "paged": nextPage
            },
            success: function (response) {
                console.log(response);
                if(response.success){
                    $(".reply-box").append(response.data.comments_html);
                    $btnMoreP.replaceWith(response.data.pagination_html);
                }
            },error:function(){
                console.log("Lỗi");
            }
        });
    })


});