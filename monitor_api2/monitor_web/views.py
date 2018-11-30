from django.http import HttpResponse, JsonResponse
from django.views.decorators.csrf import csrf_exempt
from rest_framework import viewsets
from rest_framework.decorators import api_view, permission_classes
from rest_framework.parsers import JSONParser
from rest_framework.permissions import IsAuthenticated
from monitor_web.models import Server, UserProfile

# Create your views here.
from monitor_web.serializers import ServerSerializer, UserProfileSerializer


def index(request):
    return HttpResponse("hello")


@csrf_exempt
@api_view(['GET'])
@permission_classes((IsAuthenticated, ))
def server_list(request):
    """
    List all code snippets, or create a new snippet.
    """
    if request.method == 'GET':
        servers = Server.objects.all()
        serializer = ServerSerializer(servers, many=True)
        return JsonResponse(serializer.data, safe=False)

    elif request.method == 'POST':
        data = JSONParser().parse(request)
        serializer = ServerSerializer(data=data)
        if serializer.is_valid():
            serializer.save()
            return JsonResponse(serializer.data, status=201)
        return JsonResponse(serializer.errors, status=400)


class UserViewSet(viewsets.ModelViewSet):
    """
    API endpoint that allows users to be viewed or edited.
    """
    queryset = UserProfile.objects.all()
    serializer_class = UserProfileSerializer
