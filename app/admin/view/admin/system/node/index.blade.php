@include('admin.layout.head')
<div class="layuimini-container">
    <div class="layuimini-main">
        <table id="currentTable" class="layui-table layui-hide"
               data-auth-add="{{auths('system/node/add')}}"
               data-auth-edit="{{auths('system/node/edit')}}"
               data-auth-delete="{{auths('system/node/delete')}}"
               data-auth-password="{{auths('system/node/password')}}"
               lay-filter="currentTable">
        </table>
    </div>
</div>
@include('admin.layout.foot')
