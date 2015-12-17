<h1>Submit page</h1>
<table>
<?php foreach ($_POST as $key => $value): ?>
    <tr>
        <td>
            <?php echo $key ?>
        </td>
        <td id="<?php echo $key ?>">
            <?php echo $value ?>
        </td>
    </tr>
<?php endforeach ?>
</table>
