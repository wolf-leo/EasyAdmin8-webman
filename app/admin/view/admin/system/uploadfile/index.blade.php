@include('admin.layout.head')
<div class="layuimini-container">
    <div class="layuimini-main">
        <table id="currentTable" class="layui-table layui-hide"
               data-auth-add="{{auths('system/uploadfile/add')}}"
               data-auth-edit="{{auths('system/uploadfile/edit')}}"
               data-auth-delete="{{auths('system/uploadfile/delete')}}"
               lay-filter="currentTable">
        </table>
    </div>
</div>
<script>
    let upload_types = '{!! json_encode($upload_types) !!}';
    upload_types = JSON.parse(upload_types)
</script>
@include('admin.layout.foot')
