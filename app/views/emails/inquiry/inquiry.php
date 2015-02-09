<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Urhome Inquiry</title>
    </head>
    <body style="background-color: #eff0ef;font-family: 'Arial'">
        <table style="width: 650px;margin: 20px auto 5px auto;background-color: #fff;border-collapse:collapse;">
            <tr>
                <td style="padding:20px">


                    Hi <?= $inquiry['name'] ?>, <br/>
                    <br/>
                    Donec lobortis risus a elit. Etiam tempor. Ut ullamcorper, ligula eu tempor congue, eros 
                    est euismod turpis, id tincidunt sapien risus a quam. Maecenas fermentum consequat mi.<br/>
                    <br/>
                    <br/>
                    Thank you,
                    <div style="margin: 10px 0">
                        <img src="http://res.cloudinary.com/urhome-ph/image/upload/c_crop,h_210,w_210,x_304,y_60/v1/users/1/1-2-1419534561" style="border-bottom: 5px solid orange;width: 40px;float:left;margin-right:10px"/>
                        <label style="display:block;font-weight: bold;font-size: 18px"><??></label>
                        <span style="color: #999999"><? print_r($property);?></span>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="background-color: #61BD6D;padding: 10px 20px;">
                    <img src="http://res.cloudinary.com/urhome-ph/image/upload/v1423481696/assets/urhome-inverse-line-small.png" style="height: 30px"/>
                </td>
            </tr>
            <tr>
                <td style="background-color: #41A85F;padding: 10px;font-size:12px">
                    <a style="color: #ffffff;margin: 0 10px">Homes</a>
                    <a style="color: #ffffff;margin: 0 10px">Sell</a>
                    <a style="color: #ffffff;margin: 0 10px">Property Management</a>
                    <a style="color: #ffffff;margin: 0 10px">Advice</a>
                    <a style="color: #ffffff;margin: 0 10px">Agents</a>

                    <a style="float:right;color: #ffffff;margin: 0 10px">Unsubscribe</a>
                </td>
            </tr>
        </table>
        <div style="text-align: center;color: #999999;font-size: 11px">
            Copyright &copy; 2015 Urhome.ph | All Rights Reserved
        </div>
    </body>
</html>
