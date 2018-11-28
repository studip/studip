<?= MessageBox::error("THIS IS JUST AN EXAMPLE TEXT. DON'T USE UNMODIFIED.") ?>
<table width="100%" border="0" cellspacing="10" cellpadding="10">
    <colgroup>
        <col style="width: 47%">
        <col style="width: 6%">
        <col style="width: 47%">
    </colgroup>
    <tr>
        <td colspan="3">
            <h1>Changes of visibility in Stud.IP</h1>
            <p>From now your data is only accessible for other users with your permission.<br>
                You <em>now</em> have the possibility to decide whether to be visible as usual and to use Stud.IP like
                before or to be invisible without being able to use all of the functions. </p>
            <p>If you look into your personal details you can change these adjustments at any time.</p>
        </td>
    </tr>
    <tr>
        <td valign="top">If you are visible you</td>
        <td></td>
        <td valign="top">If you are invisible you</td>
    </tr>
    <tr>
        <td valign="top">
            <ul>
                <li>can use any opportunity of communication</li>
                <li>can decide most extensively what information other users are able to see on your personal homepage
                </li>
                <li>can be found by other users and come into contact with them</li>
                <li>make a contribution to help making Stud.IP an active and communicative platform</li>
            </ul>
        </td>
        <td></td>
        <td valing="top">
            <ul>
                <li>cannot be found via "user search"</li>
                <li>don't appear in the "who is online"-list</li>
                <li>cannot use your Stud.IP-homepage anymore</li>
                <li>don't give the opportunity to other users for reaching your e-mail address, your Stud.IP-score,
                    etc.
                </li>
                <li>cannot be listed into the address-books of other users</li>
            </ul>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            Careful! In principle and regardless of being visible or invisible , some things are valid:
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <ul>
                <li>you can use Stud.IP actively and take part in courses, forum discussions, etc.
                <li>participant-lists of courses are only accessible for the participants if everyone of them is in
                    agreement with it
                <li>as soon as you become active in the system - which means creating articles in forum discussions,
                    taking part in non-invisible polls (votings), sending mails, etc. - your name is indicated and it
                    isn't possible to avoid that other users can see directly whether you are visible or invisible.
            </ul>
        </td>
    </tr>
    <tr>
        <td style="background:#ddffdd; border:1px solid black;" valign="top">
            <p><b>I want to be visible and want to use any opportunity given by Stud.IP.</b></p>
            <p>I accept that the information - listed in the <a
                        href="<?= URLHelper::getURL('datenschutz.php') ?>" class="link-intern"
                        target="_blank">explanations of data protection</a> - are accessible for others.</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <p>&nbsp;</p>
            <?= \Studip\LinkButton::create('Become visible', URLHelper::getURL('?vis_state=yes&vis_cmd=apply')) ?>
        </td>
        <td></td>
        <td style="background:#ffdddd; border:1px solid black;" valign="top">
            <p><b>I want to be invisible and put up with using restrictions. </b>
            <p>I note that my personal data - as described and well-founded in the <a
                        href="<?= URLHelper::getLink('datenschutz.php') ?>" class="link-intern"
                        target="_blank">explanation of data protection</a> - is nevertheless accessible for
                administrators and partly for lecturers of my courses.</p>
            <?= \Studip\LinkButton::create('Become visible', URLHelper::getURL('?vis_state=no&vis_cmd=apply')) ?>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <p>You are able to change this decision at any time if you look into your personal details.
        </td>
    </tr>
</table>
