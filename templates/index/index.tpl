<!DOCTYPE html>
<html lang="de" dir="ltr">
{{block=index/head}}
<body>

    <div class="row">

        <div class="col-2 col-m-3"></div>

        <div class="col-8 col-m-6">

            {{foreach users, key = user}}

                <div class="col-4 col-m-4">
                    <div class="user">
                        <img src="{{t $baseUrl}}/templates/media/images/user.png" alt="user.png">
                        <p>{{text $user.name}}</p>
                    </div>
                </div>
            
            {{end foreach}}
    
        </div>

    </div>

</body>

</html>