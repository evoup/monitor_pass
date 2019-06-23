from rest_framework.pagination import PageNumberPagination

from web.common import constant
from web.common.order import getOrderList


class CustomPageNumberPagination(PageNumberPagination):
    """
    自定义分页类
    """
    #每页显示多少个
    page_size = 8
    #默认每页显示3个，可以通过传入pager1/?page=2&size=4,改变默认每页显示的个数
    page_size_query_param = "size"
    #最大页数不超过10000
    max_page_size = 10000
    #获取页码数的
    page_query_param = "page"

    def __init__(self, foo):
        self.page_size = foo


def paging_request(request, model, obj, filter=None):
    """
    获取经过分页的请求数据
    :param request: request
    :param model: models.对象
    :param obj: view对象
    :return: 分页数据
    """
    order_list, prop = getOrderList(request)
    # 获取所有数据
    if filter is None:
        records = model.objects.all() if prop == '' else model.objects.order_by(*order_list)
    else:
        # https://stackoverflow.com/questions/2932648/how-do-i-use-a-string-as-a-keyword-argument
        records = model.objects.filter(**filter) if prop == '' else model.objects.filter(**filter).order_by(*order_list)

    # 创建分页对象，这里是自定义的MyPageNumberPagination
    page_handler = CustomPageNumberPagination(request.GET.get('size', constant.DEFAULT_PAGE_SIZE))
    # 获取分页的数据
    page_data = page_handler.paginate_queryset(queryset=records, request=request, view=obj)
    return page_data, len(records)
