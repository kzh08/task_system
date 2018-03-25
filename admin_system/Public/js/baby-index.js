if (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0 || navigator.userAgent.toLowerCase().indexOf("safari") >= 0) {
        window.setInterval(function() {
            $('input:-webkit-autofill').each(function() {
                var clone = $(this).clone(true, true);
                $(this).after(clone).remove();
            });
        }, 16);
    }

    $(function() {
        $(".baby-center").keyup(function(e) {
            if ($("#password").val().length > 0) {
                $(".circle1").addClass("show");
            } else {
                $(".circle1").removeClass("show");
            }

            if (e.keyCode === 13) {
                if ($("#password").val().length > 0 && $("#username").val().length > 0) {
                    babyLogin();
                } else {
                    if ($("#username").val().length === 0) {
                        $("#username").focus();
                    }
                    if ($("#password").val().length === 0) {
                        $("#password").focus();
                    }
                }
            }
        });

        $("#password").focus(function() {
            if ($("#password").val().length > 0) {
                $(".circle1").addClass("show");
            }
        });
        $(".circle1").click(function() {
            babyLogin();
        });

        babyLogin = function() {
            var pass     = $("#password").val();
            var user     = $("#username").val();
            var pre_url  = $(".url").val();
            var data = {
                user: user,
                pass: pass
            }
            var url = pre_url +'/Login/check';
            $.post(url, data, function(da) {
                if (da != 'success') {
                    var el = $(".bottle"),
                        newOne = el.clone(true);
                    el.before(newOne);
                    $(newOne).addClass("wobble");
                    $(".bottle" + ":last").remove();

                } else {
                    location.href = pre_url + '/Index/index';
                }
            })
        }

        // $('.bg')[0].src=$('.bg')[0].src.slice(0,-7)+'bg'+Math.floor(Math.random()*5)+'.jpg'

    })
