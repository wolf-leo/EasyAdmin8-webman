@include('admin.layout.head')
<div class="layuimini-container">
    <div class="layuimini-main">
        <table id="currentTable" class="layui-table layui-hide"
               data-auth-add="{{auths('system/auth/add')}}"
               data-auth-edit="{{auths('system/auth/edit')}}"
               data-auth-delete="{{auths('system/auth/delete')}}"
               data-auth-password="{{auths('system/auth/password')}}"
               lay-filter="currentTable">
        </table>
    </div>
</div>
@include('admin.layout.foot')
