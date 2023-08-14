@include('admin.layout.head')
<div class="layuimini-container">
    <div class="layuimini-main">
        <table id="currentTable" class="layui-table layui-hide"
               data-auth-add="{:auth('mall/cate/add')}"
               data-auth-edit="{:auth('mall/cate/edit')}"
               data-auth-delete="{:auth('mall/cate/delete')}"
               data-auth-stock="{:auth('mall/cate/stock')}"
               lay-filter="currentTable">
        </table>
    </div>
</div>
@include('admin.layout.foot')
