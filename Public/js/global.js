//全局函数
var globalFn = {
    //全选
    checkBoxSecAll:function(bool){
        $(".all_pnumber_child").each(function(i){
            !bool?$(this).prop('checked',false):$(this).prop('checked',true);
        })
    },

    //不全选
    checkBoxSecNotAll:function(){
        var lgh1 = $(".all_pnumber_child:checked").length;
        var lgh2 = $(".all_pnumber_child").length;
        if(lgh2 > lgh1) $('#all_pnumber').prop('checked',false);
        if(lgh2 == lgh1) $('#all_pnumber').prop('checked',true);

    },
    //page  总页数
    //jumpurl 模块/控制器/方法
    lypage:function (id, page, jumpUrl, field, val) {
        layui.use('laypage', function () {
            var laypage = layui.laypage;
            laypage({
                cont: id,
                pages: page,
                skip: true,
                curr: function () { //通过url获取当前页，也可以同上（pages）方式获取
                    var page = location.search.match(/page=(\d+)/);
                    return page ? page[1] : 1;
                }(),
                jump: function (obj, first) { //触发分页后的回调
                    if (!first) { //一定要加此判断，否则初始时会无限刷新
                        var currentPage = obj.curr;//获取点击的页码
                        window.location.href = "/index.php/"+jumpUrl+".html?page=" + currentPage+'&'+field+'='+val;
                    } else {
                    }
                }
            });
        })
    },

    //ly弹窗封装
    lyPopup:function(title, area, id, close, shadeClose){
        layui.use('layer', function(){
            var layer = layui.layer;
            layer.open({
                title: title,
                type: 1,
                area: area,
                scrollbar: false,
                content: $('#'+id),
                closeBtn: close,
                shadeClose: shadeClose ? true : false,
            });
        });
    },

    //layer单选
    checkRadio:function(radioVal){
            $('.layui-form-radio').each(function(i){
                //var lyi = $($(this).children("i").get(0));
                var lyi = $($(this).children("i"));
                if(i == radioVal){
                    !$(this).hasClass('layui-form-radioed') && $(this).addClass('layui-form-radioed');
                    !lyi.hasClass('layui-anim-scaleSpring') && lyi.addClass('layui-anim-scaleSpring');
                    lyi.html('&#xe643;');
                }else{
                    $(this).hasClass('layui-form-radioed') && $(this).removeClass('layui-form-radioed');
                    lyi.hasClass('layui-anim-scaleSpring') && lyi.removeClass('layui-anim-scaleSpring');
                    lyi.html('&#xe63f;');
                }

            })

    },

    //ajax回调提醒
    remind:function(index, info, jumpUrl){
        setTimeout(function () { layer.close(index); }, 1000);
        setTimeout(function () { layer.msg(info) }, 1000);
        jumpUrl && setTimeout(function(){window.location.href = jumpUrl}, 2000);
    },

    //上传图片
    webUpload:function(idArr, file){
        // 初始化Web Uploader
        var uploader = WebUploader.create({

            // 选完文件后，是否自动上传。
            auto: true,
            // swf文件路径
            swf: '/Public/js/webuploader-0.1.5/Uploader.swf',
            // 文件接收服务端。
            server: "/index.php/Admin/Base/upload.html?path="+file,
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#filePicker',
            // 只允许选择图片文件。
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/jpg,image/jpeg,image/png'
            }
        });

        // 文件上传成功
        uploader.on( 'uploadSuccess', function( file, res) {
            $('#'+idArr[0]).val(res.bigImg); //大图地址  input
            $('#'+idArr[1]).val(res.thumb); //缩略图地址 input
            idArr[2] && $('#'+idArr[2]+' img').attr('src', res.thumb);
            idArr[3] && $('#'+idArr[3]+' img').attr('src', res.thumb);
            globalFn.popupMsg('上传成功');
            console.log();
        });

        // 文件上传失败，显示上传出错。
        uploader.on( 'uploadError', function( file ) {
            globalFn.popupMsg('上传失败');
        });
    },

    //弹窗msg
    popupMsg:function(text){
        layui.use('layer', function(){
            layui.layer.msg(text);
        });
    },

    //预览
    preview: function(id){
        $('#preview').click(function(){
            $('#'+id+' img').attr('src')
                ? globalFn.lyPopup(false, ['auto'], id, 0, true)
                : globalFn.popupMsg('请先上传图片');
        })
    },

    //点击图片放大
    zoomify:function(){
        //缩小绑定事件隐藏
        var $zoomify = $('.example img');
        $zoomify.zoomify().on({
            'zoom-out-complete.zoomify': function() {
                $('.example img').hide();
            },
        });

        //点击放大的方法
        $('.zoomIn').on('click', function() {
            var _thisId = '#big'+$(this).attr('id');
            $(_thisId+' img').show();
            $(_thisId+' img').zoomify('zoomIn');
        });
    },

    //lyform
    lyformRender:function(type){
        layui.use(['form'], function(){
            var form = layui.form();
            form.render(type)
        });
    },

    /**
     * 实时动态强制更改用户录入
     * arg1 inputObject
     **/
    amount:function(th){
        var regStrs = [
            ['^0(\\d+)$', '$1'], //禁止录入整数部分两位以上，但首位为0
            ['[^\\d\\.]+$', ''], //禁止录入任何非数字和点
            ['\\.(\\d?)\\.+', '.$1'], //禁止录入两个以上的点
            ['^(\\d+\\.\\d{5}).+', '$1'] //禁止录入小数点后两位以上
        ];
        for(i=0; i<regStrs.length; i++){
            var reg = new RegExp(regStrs[i][0]);
            th.value = th.value.replace(reg, regStrs[i][1]);
        }
    },

    /**
     * 录入完成后，输入模式失去焦点后对录入进行判断并强制更改，并对小数点进行0补全
     * arg1 inputObject
     * 这个函数写得很傻，是我很早以前写的了，没有进行优化，但功能十分齐全，你尝试着使用
     * 其实有一种可以更快速的JavaScript内置函数可以提取杂乱数据中的数字：
     * parseFloat('10');
     **/
    overFormat:function(th){
        var v = th.value;
        if(v === ''){
            v = '0.00';
        }else if(v === '0'){
            v = '0.00';
        }else if(v === '0.'){
            v = '0.00';
        }/*else if(/^0+\d+\.?\d*.*$/.test(v)){
            v = v.replace(/^0+(\d+\.?\d*).*$/, '$1');
            v = inp.getRightPriceFormat(v).val;
        }else if(/^0\.\d$/.test(v)){
            v = v + '0';
        }else if(!/^\d+\.\d{2}$/.test(v)){
            if(/^\d+\.\d{2}.+/.test(v)){
                v = v.replace(/^(\d+\.\d{2}).*$/, '$1');
            }else if(/^\d+$/.test(v)){
                v = v + '.00';
            }else if(/^\d+\.$/.test(v)){
                v = v + '00';
            }else if(/^\d+\.\d$/.test(v)){
                v = v + '0';
            }else if(/^[^\d]+\d+\.?\d*$/.test(v)){
                v = v.replace(/^[^\d]+(\d+\.?\d*)$/, '$1');
            }else if(/\d+/.test(v)){
                v = v.replace(/^[^\d]*(\d+\.?\d*).*$/, '$1');
                ty = false;
            }else if(/^0+\d+\.?\d*$/.test(v)){
                v = v.replace(/^0+(\d+\.?\d*)$/, '$1');
                ty = false;
            }else{
                v = '0.00';
            }
        }*/
        th.value = v;
    }
}



/*layui.use('laypage', function () {
 var laypage = layui.laypage;
 laypage({
 cont: 'demo7',
 pages: "<?php echo $data['pages']?>",
 skip: true,
 curr: function () { //通过url获取当前页，也可以同上（pages）方式获取
 var page = location.search.match(/page=(\d+)/);
 return page ? page[1] : 1;
 }(),
 jump: function (obj, first) { //触发分页后的回调
 if (!first) { //一定要加此判断，否则初始时会无限刷新
 var currentPage = obj.curr;//获取点击的页码
 window.location.href = "/index.php/Admin/SystemLink/index.html?page=" + currentPage;
 } else {
 }
 }
 });
 })*/