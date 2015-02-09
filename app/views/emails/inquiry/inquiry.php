<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Urhome Inquiry</title>
    </head>
    <body style="background-color: #eff0ef;font-family: 'Arial';padding: 20px 0px">
        <table style="width: 650px;margin: 0 auto 5px auto;background-color: #fff;border-collapse:collapse;">
            <tr>
                <td style="padding:20px">
                    <?
                    $alias = $property['alias'];
                    ?>
                    Hi <?= $inquiry['name'] ?>, <br/>
                    <br/>
                    Donec lobortis risus a elit. Etiam tempor. Ut ullamcorper, ligula eu tempor congue, eros 
                    est euismod turpis, id tincidunt sapien risus a quam. Maecenas fermentum consequat mi.<br/>
                    <br/>
                    <div style="padding:10px;background-color:#f9f9f9;border-radius: 4px">
                        <a href="">
                            <img src="<?= $propertyPhoto['url']?>" style="width:180px;float:left;margin-right: 10px"/>
                        </a>
                        <h2 style="margin: 0px;font-weight: normal"><?= $property['address_name']?></h2>
                        <span style="color: #999999;display:block"><i><?= $property['tagline']?></i></span>

                        <span style="background-color: #999;color: #efefef;padding: 2px 10px;border-radius: 3px;font-size: 10px;display:inline-block"><?= $property['status_name']?></span>
                        <br/>
                        <p><?= $types?></p>
                        <br/>
                        <a href="<?= URL::to("http://urhome.dev/property/$alias")?>" style="margin: 10px 0;background-color: #FBA026;padding: 10px 15px;color: #ffffff;border-radius: 4px;text-decoration: none">View Details</a>

                        <div style="clear:both"></div>
                    </div>
                    <br/>
                    Thank you,
                    <div style="margin: 10px 0">
                        <img src="<?= $agentPhotoUrl?>" style="border-bottom: 5px solid #FBA026;width: 40px;float:left;margin-right:10px"/>
                        <label style="display:block;font-weight: bold;font-size: 18px"><?= $agent['name']?></label>
                        <span style="color: #999999"><?= $agent['title']?></span>
                        <div style="clear:both"></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="background-color: #61BD6D;padding: 10px 20px;">
                    <img src="http://res.cloudinary.com/urhome-ph/image/upload/v1423481696/assets/urhome-inverse-line-small.png" style="width: 150px"/>
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
            Copyright &copy; 2015 Urhome.ph. All Rights Reserved
        </div>
    </body>
</html>
