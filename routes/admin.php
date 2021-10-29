<?php

Route::namespace('Admin\System')
//    ->middleware('web')
    ->group(function () {
        # 配置列表
        Route::get('system/configList', 'ConfigController@list');
        # 登录
        Route::post('system/login', 'LoginController@login');

    });

# 系统
Route::namespace('Admin\System')
    ->middleware('auth.admin')
    ->group(function () {

        # 用户列表
        // 导航栏点击左侧用户列表
        Route::get('system/userlist', 'UserController@userList');
        // 用户列表点击添加用户
        Route::post('system/useradd', 'UserController@userAdd');
        // 用户列表点击修改用户
        Route::post('system/usermodify', 'UserController@userModify');
        // 管理员重置user密码
        Route::post('system/resetpassword', 'UserController@resetPassword');
        // 用户修改：登录后（忘记：登录前）密码
        Route::post('system/modifyOrForgetPassword', 'UserController@modifyOrForgetPassword');
        // 每隔一段时间删除数据库的token
        Route::get('system/delDatabaseToken', 'UserController@delDatabaseToken');


        # 角色列表
        Route::get('system/rolelist', 'RoleController@roleList');
        // 角色列表点击添加角色
        Route::post('system/roleadd', 'RoleController@createOrUpdate');
        // 角色列表点击修改角色
        Route::post('system/rolemodify', 'RoleController@createOrUpdate');
        // 角色列表点击删除角色
        Route::post('system/roledel', 'RoleController@roleDel');
        //修改角色菜单权限
        Route::post('system/modifymenuauthority', 'RoleController@menuAuthority');
        // 判断角色权限
        Route::post('system/judgerolepower', 'RoleController@judgeRolePower');

        # Action列表
        Route::get('system/actionlist', 'ActionController@actionList');
        // Action列表点击添加Action
        Route::post('system/actionadd', 'ActionController@createOrUpdate');
        // Action列表点击修改Action
        Route::post('system/actionmodify', 'ActionController@createOrUpdate');
        // 管理员分配user权限时传递给前端展示的数据
        Route::get('system/actionShow', 'ActionController@actionShow');


        # 菜单列表
        Route::get('system/menulist', 'MenuController@menuList');
        // 菜单列表点击添加模板
        Route::post('system/menuadd', 'MenuController@createOrUpdate');
        // 菜单列表点击修改模板
        Route::post('system/menumodify', 'MenuController@createOrUpdate');
        // 菜单列表获取顶级模板
        Route::get('system/getparentid', 'MenuController@getParentIDs');
        // 管理员给role分配权限时传递给前端展示的数据
        Route::get('system/menuShow', 'MenuController@menuShow');

        # 配置列表
        Route::get('system/sysconfiglist', 'SysConfigController@sysConfigList');
        // 配置列表点击添加配置
        Route::post('system/sysconfigadd', 'SysConfigController@createOrUpdate');
        // 配置列表点击修改配置
        Route::post('system/sysconfigmodify', 'SysConfigController@createOrUpdate');

        # 轮播列表
        Route::get('system/carouselList', 'CarouselController@carouselList');
        // 添加修改
        Route::post('system/carouselCreateOrUpdate', 'CarouselController@createOrUpdate');

        # 退出
        Route::post('system/logout', 'LoginController@logout');
    });



# 供应商
Route::namespace('Admin\Supplier')
    ->middleware('auth.admin')
    ->group(function () {
        #供应商创建或更新
        Route::post('supplier/supplierCreateOrUpdate', 'SupplierController@createOrUpdate');
        #供应商列表
        Route::get('supplier/supplierList', 'SupplierController@list');
        #地方
        Route::get('supplier/placeList/{id}', 'SupplierController@placeList');

        #供应商信息创建或更新
        Route::post('supplier/supplierInfoCreateOrUpdate', 'SupplierInformationController@createOrUpdate');
        #供应商信息列表
        Route::get('supplier/supplierInfoList', 'SupplierInformationController@list');
    });


#商户
Route::namespace('Admin\Merchant')
    ->middleware('auth.admin')
    ->group(function () {
        #商户创建或更新
        Route::post('merchant/merchantCreateOrUpdate', 'MerchantController@createOrUpdate');
        #商户列表
        Route::get('merchant/merchantList', 'MerchantController@list');

        #商户地址创建或更新
        Route::post('merchant/merchantAddressCreateOrUpdate', 'MerchantAddressController@createOrUpdate');
        #商户地址列表
        Route::get('merchant/merchantAddressList', 'MerchantAddressController@list');



    });

#店铺
Route::namespace('Admin\Shop')
    ->middleware('auth.admin')
    ->group(function () {

        #店铺创建或更新
        Route::post('shop/shopCreateOrUpdate', 'ShopController@createOrUpdate');
        #店铺列表
        Route::get('shop/shopList', 'ShopController@list');

        #店铺收款创建或更新
        Route::post('shop/shopCollectionCreateOrUpdate', 'ShopCollectionController@createOrUpdate');
        #店铺收款列表
        Route::get('shop/shopCollectionList', 'ShopCollectionController@list');

        #店铺费用创建或更新
        Route::post('shop/shopExpensesCreateOrUpdate', 'ShopExpensesController@createOrUpdate');
        #店铺费用列表
        Route::get('shop/shopExpensesList', 'ShopExpensesController@list');

        #店铺产品创建或更新
        Route::post('shop/shopProductCreateOrUpdate', 'ShopProductController@createOrUpdate');
        #店铺产品列表
        Route::get('shop/shopProductList', 'ShopProductController@list');

        #店铺产品库存创建或更新
        Route::post('shop/shopProductStockCreateOrUpdate', 'ShopProductStockController@createOrUpdate');
        #店铺产品库存列表
        Route::get('shop/shopProductStockList', 'ShopProductStockController@list');

    });

# 产品
Route::namespace('Admin\Product')
//    ->middleware('auth.admin')
    ->group(function () {
        #产品创建或更新
        Route::post('product/productCreateOrUpdate', 'ProductController@createOrUpdate');
        #产品列表
        Route::get('product/productList', 'ProductController@list');

        #产品条码创建或更新
        Route::post('product/productBarcodeCreateOrUpdate', 'ProductBarcodeController@createOrUpdate');
        #产品条码列表
        Route::get('product/productBarcodeList', 'ProductBarcodeController@list');

        #产品分类创建或更新
        Route::post('product/productCategoryCreateOrUpdate', 'ProductCategoryController@createOrUpdate');
        #产品分类列表
        Route::get('product/productCategoryList', 'ProductCategoryController@list');
    });

# 订单
Route::namespace('Admin\Order')
//    ->middleware('auth.admin')
    ->group(function () {
//        #创建或修改
//        Route::get('order/orderList', 'OrderController@list');
//        // 查看订单详情
//        Route::post('order/orderDetail', 'OrderController@orderDetail');
//        // 修改订单收货信息
//        Route::post('order/modifyReceivingInfo', 'OrderController@modifyReceivingInfo');
//        // 修改未付款的订单价格
//        Route::post('order/modifyPayAmount', 'OrderController@modifyPayAmount');


        #订单创建或更新
        Route::post('order/orderCreateOrUpdate', 'orderController@createOrUpdate');
        #订单列表
        Route::get('order/orderList', 'orderController@list');
    });

# 物流
Route::namespace('Admin\Logistics')
//    ->middleware('auth.admin')
    ->group(function () {
        #物流创建或更新
        Route::post('logistics/logisticsCreateOrUpdate', 'LogisticsController@createOrUpdate');
        #物流列表
        Route::get('logistics/logisticsList', 'LogisticsController@list');
        #物流公司列表
        Route::get('logistics/getEc', 'LogisticsController@getEc');
        #物流公司列表
        Route::post('logistics/getLogisticsSearch', 'LogisticsController@getLogisticsSearch');
    });



# 促销
Route::namespace('Admin\Promotion')
//    ->middleware('auth.admin')
    ->group(function () {

    });





