<?php if (!empty($flash)): ?>
    <div style="padding:10px;background:<?= $flash['type']==='error'?'#fdd':'#dfd' ?>">
        <?= htmlspecialchars($flash['message']) ?>
    </div>
<?php endif; ?>

<h1>LabSync Login</h1>
<form method="POST" action="/LabSync-System/login">
    <label>Username: <input type="text" name="userName" required></label><br><br>
    <label>Password: <input type="password" name="userPassword" required></label><br><br>
    <button type="submit">Login</button>
</form>

<p style="margin-top:30px; color:#666; font-size:12px;">
    Test users (password = <code>password123</code>):<br>
    manager_ahmed, pi_smith, researcher_mahmoud, researcher_sara
</p>