function CheckInternetExplorer() {
    var bVersion = navigator.appVersion;
    var version = bVersion.split(";");
    if (version.length > 1) {
        var trimVersion = parseInt(version[1].replace(/[ ]/g, "").replace(/MSIE/g, ""));
        var $body = document.getElementsByTagName("body")[0];
        var msg = "<div style=\"text-align:center;background-color:#ff6a00;color:#fff;line-height:40px;;font-size:14px;\">您好，你当前使用的IE浏览器版本过低，为了获取更好的浏览体验，建议你使用谷歌(chrome)\火狐(Firefox)等标准浏览器，或升级IE10+以上版本！</div>";
        if (trimVersion <= 9) {
            msg += $body.innerHTML;
            $body.innerHTML = msg;
        }
    }
}

//保证所有响应式图片列表同一尺寸，避免上传图片尺寸不一致排版混乱
$(function () {
    var $imageSameSize = $(".image-same-size");
    $imageSameSize.each(function () {
        var $this = $(this);
        var $images = $this.find("img");
        if ($images.length == 0) {
            return true;
        }
        var $firstImg = $images.eq(0);
        //$(window).on("onload",function(){}) //弃用，效率太低,要等所有页面图片加载完毕才执行
        //$images.eq(0).attr("onload", function (){$images.ImageSameSize()}); //弃用火狐浏览器下获取不到响应式图片的实际高度
        var img = new Image();
        img.src = $firstImg.attr("src");
        img.onload = function () {
            $images.ImageSameSize();
        };
    });
});

//注册响应式下拉菜单
; (function ($, window, undefined) {
    $.fn.NavDropDown = function (options) {
        var $thisObj = this;
        var defaultOptions = { triggerEvent: "click", columnId: 0, navToggle: "" }
        defaultOptions = $.extend(defaultOptions, options);
        var eventName = defaultOptions.triggerEvent;
        var columnId = defaultOptions.columnId;
        var navToggle = defaultOptions.navToggle;
        var currentFirstColumn = $thisObj.find("li[data-id='" + columnId + "']");
        if (currentFirstColumn.length > 0) {
            currentFirstColumn.addClass("active");
            currentFirstColumn.parents("li").addClass("active");
        }
        var $navToggle = $(navToggle);
        var $firstUl = $thisObj.find("ul").eq(0); //第一级ul
        var $liList = $thisObj.find("li");

        //初始化
        var InitNav = function () {
            $liList.each(function (i) {
                var $this = $(this);
                var $childrenUl = $this.children("ul");
                if ($childrenUl.length > 0) {
                    $this.addClass("has-sub-nav") //如果Li有子级，则给li添加一个has-sub-nav样式
                    if ($this.hasClass("active")) {
                        $childrenUl.show();
                    }
                    else {
                        $childrenUl.hide();
                    }
                    var dataInit = $this.attr("data-init");
                    if (!dataInit) {
                        $this.prepend("<em class=\"sub-nav-toggle\"><i class=\"fa fa-caret-right fa-lg\"></i></em>");
                        $this.attr("data-init", "true");
                    }
                };
            });
        }
        InitNav();
        var $navToggles = $thisObj.find(".sub-nav-toggle");
        var Start = function () {
            var window_width = $(document).width();
            if ($navToggle.is(':hidden')) {
                //pc端
                $firstUl.show();
                if (eventName == "click") {
                    ClickTrigger();
                }
                else {
                    $liList.each(function (i) {
                        var $this = $(this);
                        var $childrenUl = $this.children("ul");
                        if ($childrenUl.length > 0) {
                            $childrenUl.hide();
                        };
                    });
                    $navToggles.hide();
                    MouseEnterTrigger();
                }
            }
            else {
                //手机端
                $navToggles.show();
                //$firstUl.hide(); //safari浏览器中导致上下滑动隐藏,改为css控制
                ClickTrigger();
            }
        };
        //鼠标点击触发事件
        function ClickTrigger() {
            $liList.off("mouseenter mouseleave");
            $navToggles.each(function () {
                var $this = $(this);
                $this.off("click").on("click", function () {
                    var $thisParent = $this.parent("li");
                    if ($thisParent.hasClass("active")) {
                        $thisParent.removeClass("active");
                        $thisParent.children("ul").stop().slideUp();
                    } else {
                        $thisParent.addClass("active");
                        $thisParent.children("ul").stop().slideDown();
                    }
                })
            })
        }
        //鼠标移动触发展开事件
        function MouseEnterTrigger() {
            $liList.each(function () {
                var $this = $(this);
                $this.off("mouseenter").on("mouseenter", function () {
                    $this.children("ul").stop().slideDown();
                }).off("mouseleave").on("mouseleave", function () {
                    $this.children("ul").stop().slideUp();
                });
            })
        }
        Start();
        $(window).resize(function () {
            InitNav();
            Start();
        });
        //注册触发图标的点击事件
        if (navToggle != "") {
            if ($navToggle.length == 0) {
                alert(navToggle + "对象为空");
            }
            $navToggle.off("clicks").on("click", function () {
                var $this = $(this);
                $firstUl.stop().slideToggle();
                if ($this.hasClass("active")) {
                    $this.removeClass("active");
                }
                else {
                    $this.addClass("active");
                }
            })
        }
    }
})(jQuery, window);


//自动适应尺寸，如果容器列表项内有图片需要自动适应宽度和高度，所有容器中的图片文件尺寸默认用同级第一个容器尺寸。
; (function ($, window, undefined) {
    $.fn.ImageSameSize = function (index) {
        var $objs = this;
        if (index == undefined) {
            index = 0;
        }
        var $templateImage = $objs.eq(index);
        var SetSize = function () {
            var height = $templateImage.css("height").replace(/\s+|px/gi, "");
            var width = $templateImage.css("width").replace(/\s+|px/gi, "");
            $objs.each(function (idx) {
                $(this).css({ "width": width + "px", "height": height + "px" });
            });
        }
        SetSize();
        var bindResize = $templateImage.attr("data-bindResize");
        if (bindResize == undefined) {
            $(window).resize(function () {
                $templateImage.css({ "width": "auto", "height": "auto" });
                SetSize();
            });
            $templateImage.attr("data-bindresize", 1);
        }
    };
})(jQuery, window);



  
wow = new WOW({
    animateClass: 'animated',
});
wow.init();



