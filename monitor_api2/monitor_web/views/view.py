import logging

from django.http import HttpResponse

# Create your views here.

logger = logging.getLogger(__name__)


def index(request):
    return HttpResponse("hello")
