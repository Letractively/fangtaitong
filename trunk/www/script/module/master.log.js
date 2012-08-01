Z.Module.master = Z.Module.master || {};
Z.Module.master.log = Z.Module.master.log || {};

Z.Module.master.log.order = Z.Module.master.log.order || {};
Z.Module.master.log.order.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
    $('#form-log-order-index').validate();
};

Z.Module.master.log.bill = Z.Module.master.log.bill || {};
Z.Module.master.log.bill.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
    $('#form-log-bill-index').validate();
};

Z.Module.master.log.room = Z.Module.master.log.room || {};
Z.Module.master.log.room.init = function()
{
    Z.Global.method.tierLook('.data tbody tr');
    $('#form-log-room-index').validate();
};
