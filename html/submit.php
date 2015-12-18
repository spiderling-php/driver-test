<h1>Submit page</h1>
<table>
<?php foreach ($_POST as $key => $value): ?>
    <tr>
        <td><?php echo $key ?></td>
        <td id="<?php echo $key ?>"><?php echo $value ?></td>
    </tr>
<?php endforeach ?>
</table>

<table>
<?php foreach ($_FILES as $name => $value): ?>
    <tr>
        <td><?php echo $name ?></td>
        <td id="<?php echo $name ?>-name"><?php echo $value['name'] ?></td>
        <td id="<?php echo $name ?>-error"><?php echo $value['error'] ?></td>
        <td id="<?php echo $name ?>-size"><?php echo $value['size'] ?></td>
    </tr>
<?php endforeach ?>
</table>

