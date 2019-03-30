class MyCustomValidators(object):
    '''
    Class-based validator:
    http://www.django-rest-framework.org/api-guide/validators/#class-based
    https://stackoverflow.com/questions/32151056/write-a-validator-class-in-django-rest-framework-that-has-access-to-the-validata
    https://stackoverflow.com/a/43660815
    '''

    def __call__(self, *args, **kwargs):
        print("custom validators in Meta class")
        # if value % self.base != 0:
        #     message = 'This field must be a multiple of %d.' % self.base
        #     raise serializers.ValidationError(message)
