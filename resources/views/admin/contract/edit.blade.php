{{-- 继承主要布局模板 --}}
@extends('admin/layouts/main')

{{-- 插入到<head>的代码 --}}
@section('head')
    <title>{{env('SITENAME')}}</title>

    <link rel="stylesheet" type="text/css" href="/resource/css/uploadify.css">

    <script>
        $.extend(ST.ACTION, {
            delFile :"{{ route('admin.contract.api.delete-file') }}",//删除文件的接口
            contractUpload: "{{ route('admin.upload.file') }}", //上传接口
        });

        {{-- 编辑或是创建合同？ --}}
        @if (isset($contract))
        $.extend(ST.ACTION, {
                    contractDeatil: "{{ route('admin.contract.api.get-form-data', ['id' => $contract->id]) }}",
                    editContract  : "{{ route('admin.contract.update', ['id' => $contract->id]) }}"
                });

        @else
            $.extend(ST.ACTION, {
                    contractDeatil: "{{ route('admin.contract.api.get-form-data') }}",
                    editContract  : "{{ route('admin.contract.store') }}"
                });
        @endif
    </script>
@endsection

{{-- 内容 --}}
@section('content')

    <div class="heading clearfix">
        <div class="pull-left">
            @if (isset($contract))
                <h2>合同管理>修改合同</h2>
            @else
                <h2>合同管理>创建合同</h2>
            @endif
        </div>
    </div>

    <div id="js-container"></div>

@endsection

{{-- 插入到尾部的代码 --}}
@section('end')

    <script>
        seajs.use('view/contract/add', function (app) {
            app({
                el: '#js-container'
            });
        });
    </script>

@endsection