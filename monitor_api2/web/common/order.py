def getOrderList(request):
    prop = request.GET.get('prop', '')
    orderList = [('-' if request.GET.get('order', '') == 'descending' and prop != '' else '') + prop]
    return orderList, prop
