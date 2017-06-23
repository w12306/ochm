@if ($log->mainObject === null)
    <a href="#">---</a>
@elseif ($log->module == 'business')
    {{-- 业务 --}}
    <a href="{{ route('admin.business.business-detail', ['id' => $log->mainObject->id]) }}">
        业务 {{ $log->mainObject->business_key }}
    </a>
@elseif ($log->module == 'delivery')
    {{-- 业务小组执行额 --}}
    <a href="#">业务小组执行额 {{ $log->main_id }}</a>
@elseif ($log->module == 'contract')
    {{-- 合同 --}}
    <a href="{{ route('admin.contract.edit', ['id' => $log->mainObject->id]) }}">
        合同 {{ $log->mainObject->ckey }}
    </a>
@elseif ($log->module == 'invoice')
    {{-- TODO 发票 --}}
    <a href="#">操作数据ID：{{$log->mainObject->invoice_key}}</a>
@elseif ($log->module == 'backcash')
    {{-- 回款 --}}
    <a href="#">
        操作数据ID： {{ $log->mainObject->backcash_key }}
    </a>
@elseif ($log->module == 'expenses')
    {{-- TODO 支出 --}}
    <a href="#">
        操作数据 支出单号： {{ $log->mainObject->expenses_key }}
    </a>
@elseif ($log->module == 'stand-expenses')
    {{-- TODO 独立支出 --}}
    <a href="#">
        操作数据 支出单号： {{ $log->mainObject->expenses_key }}
    </a>	
@elseif ($log->module == 'payment-expenses')
    {{-- TODO 付款支出 --}}
    <a href="#">
        操作数据 付款单号： {{ $log->mainObject->payment_key }}
    </a>
@elseif ($log->module == 'stand-payment-expenses')
    {{-- TODO 独立付款支出 --}}
    <a href="#">
        操作数据 付款单号： {{ $log->mainObject->payment_key }}
    </a>	
@elseif ($log->module == 'badcash')
    {{-- 坏账 --}}
    <a href="{{ route('admin.badcash.list') }}">
        坏账 {{ $log->mainObject->badcash_key }}
    </a>
@elseif ($log->module == 'earnestcash')
    {{-- 保证金 --}}
    <a href="{{ route('admin.earnestcash.edit', ['id' => $log->mainObject->id]) }}">
        保证金 {{ $log->mainObject->earnestcash_key }}
    </a>
@elseif ($log->module == 'advancecash')
    {{-- 预收款 --}}
    <a href="{{ route('admin.advancecash.edit', ['id' => $log->mainObject->id]) }}">
        预收款 {{ $log->mainObject->advancecash_key }}
    </a>	
@elseif ($log->module == 'product')
    {{-- 产品 --}}
    <a href="{{ route('admin.toolbox.product-list', ['name' => $log->mainObject->name]) }}">
        产品 {{ $log->mainObject->name }}
    </a>
@elseif ($log->module == 'company')
    {{-- 客户 --}}
    <a href="{{ route('admin.toolbox.company-list', ['company_name' => $log->mainObject->company_name]) }}">
        客户 {{ $log->mainObject->company_name }}
    </a>
@elseif ($log->module == 'under-company')
    {{-- 下游客户 --}}
    <a href="{{ route('admin.toolbox.under-company-list', ['company_name' => $log->mainObject->company_name]) }}">
        下游客户 {{ $log->mainObject->company_name }}
    </a>
@elseif ($log->module == 'partner')
    {{-- 合作方 --}}
    <a href="{{ route('admin.toolbox.partner-list', ['company_name' => $log->mainObject->company_name]) }}">
        合作方 {{ $log->mainObject->company_name }}
    </a>
@elseif ($log->module == 'under-partner')
    {{-- 下游合作方 --}}
    <a href="{{ route('admin.toolbox.under-partner-list', ['company_name' => $log->mainObject->company_name]) }}">
        下游合作方 {{ $log->mainObject->company_name }}
    </a>
@elseif ($log->module == 'permission-role')
    {{-- 权限角色 --}}
    <a href="{{ route('admin.config.permission.edit-role', ['id' => $log->mainObject->id]) }}">
        角色 {{ $log->mainObject->name }}
    </a>
@elseif ($log->module == 'admin-user')
    {{-- 管理员 --}}
    <a href="{{ route('admin.config.permission.admin-user-list', ['real_name' => $log->mainObject->real_name]) }}">
        管理员 {{ $log->mainObject->real_name }}
    </a>
@endif