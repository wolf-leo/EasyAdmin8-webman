@include('admin.layout.head')
<div class="layuimini-container">
    <div class="layuimini-main">
        <table id="currentTable" class="layui-table layui-hide"
               data-auth-add="{{auths('mall/cate/add')}}"
               data-auth-edit="{{auths('mall/cate/edit')}}"
               data-auth-delete="{{auths('mall/cate/delete')}}"
               data-auth-stock="{{auths('mall/cate/stock')}}"
               lay-filter="currentTable">
        </table>
    </div>
</div>
@include('admin.layout.foot')
