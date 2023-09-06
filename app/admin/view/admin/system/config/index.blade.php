@include('admin.layout.head')
<div class="layuimini-container">
    <div class="layuimini-main" id="app">
        <div class="layui-tab layui-tab-brief" lay-filter="docDemoTabBrief">
            <ul class="layui-tab-title">
                <li class="layui-this" data-group="site">网站设置</li>
                <li data-group="logo">LOGO配置</li>
                <li data-group="upload">上传配置</li>
            </ul>
            <div class="layui-tab-content">
                <div class="layui-tab-item layui-show">
                    @include("admin.system/config/site")
                </div>
                <div class="layui-tab-item">
                    @include("admin.system/config/logo")
                </div>
                <div class="layui-tab-item">
                    @include("admin.system/config/upload")
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin.layout.foot')
