Yii Metadata component helps to get metadata about models,controllers and actions from your application

For using you need:

1. Place this file to directory with components of your application (your\_app\_dir/protected/components)

2. Add it to 'components' in your application config (your\_app\_dir/protected/config/main.php)
> 'components'=>array(
> > 'metadata'=>array('class'=>'Metadata'),
> > > ...

> > ),

3. Use:

> $user\_actions = Yii::app()->metadata->getActions('UserController');
> var\_dump($user\_actions);

author Vitaliy Stepanenko <mail@vitaliy.in>
version 0.1
