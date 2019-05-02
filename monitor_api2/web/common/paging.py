from rest_framework.pagination import PageNumberPagination


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
