def getOrderList(request):
    prop = request.GET.get('prop', '')
    orderList = [('-' if request.GET.get('order', '') == 'descending' and prop != '' else '') + prop]
    return orderList, prop

def param_to_order(request):
    """
    根据请求参数返回是否是升序
    :param request:
    :return:
    """
    if 'order' in request.GET:
        if request.GET['order'] == 'descending':
            return False
        if request.GET['order'] == 'ascending':
            return True
    return None
