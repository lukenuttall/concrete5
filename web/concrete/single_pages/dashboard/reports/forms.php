<?php defined('C5_EXECUTE') or die("Access Denied.");
/* @var $h ConcreteDashboardHelper */
$h = Loader::helper('concrete/dashboard');
/* @var $ih ConcreteInterfaceHelper */
$ih = Loader::helper('concrete/ui');
/* @var $nh NavigationHelper */
$nh = Loader::helper('navigation');
/* @var $text TextHelper */
$text = Loader::helper('text');
/* @var $dh \Concrete\Core\Localization\Service\Date */
$dh = Loader::helper('date');
/* @var $urlhelper UrlHelper */
$urlhelper = Loader::helper('url');
/* @var $json JsonHelper */
$json = Loader::helper('json');
/* @var $valt ValidationTokenHelper */
$valt = Loader::helper('validation/token');
/* @var $db DataBase */
$db = Loader::db();
?>
<script>
    jQuery(function ($) {
        var deleteResponse = '<?= t('Are you sure you want to delete this form submission?') ?>',
            deleteForm = '<?= t('Are you sure you want to delete this form and its form submissions?') ?>',
            deleteFormAnswers = '<?= t('Are you sure you want to delete this form submissions?') ?>';
        $('.delete-response').on('click', function (e) {
            if (!confirm(deleteResponse)) {
                e.preventDefault();
            }
        });
        $('.delete-form').on('click', function (e) {
            if (!confirm(deleteForm)) {
                e.preventDefault();
            }
        });
        $('.delete-form-answers').on('click', function (e) {
            if (!confirm(deleteFormAnswers)) {
                e.preventDefault();
            }
        });
    });
</script>
<style>
    ::-webkit-scrollbar {
        -webkit-appearance: none;
        width: 7px;
        height: 6px;
    }

    ::-webkit-scrollbar-thumb {
        border-radius: 4px;
        background-color: rgba(0, 0, 0, .5);
        -webkit-box-shadow: 0 0 1px rgba(255, 255, 255, .5);
    }

    #wide-content-notification {
        margin-left: 5px;
        display: none;
        color: #aaa;
    }

    .form-results-container {
        width: 100%;
        overflow: auto;
    }

</style>
<? if (!isset($questionSet)): { ?>
    <?= $h->getDashboardPaneHeaderWrapper(t('Form Results')); ?>
    <?
    $showTable = false;
    foreach ($surveys as $qsid => $survey) {
        $block = Block::getByID((int)$survey['bID']);
        if (is_object($block)) {
            $showTable = true;
            break;
        }
    }

    if ($showTable) {
        ?>


        <table class="table table-striped">
            <thead>
            <tr>
                <th class="col-sm-5"><?= t('Form') ?></th>
                <th><?= t('Submissions') ?></th>
                <th></th>
            </tr>
            </thead>
            <tbody>

            <?php
            foreach ($surveys as $qsid => $survey) {
                $block = Block::getByID(intval($survey['bID'], 10));
                if (!is_object($block)) {
                    continue;
                }
                $in_use = (int)$db->getOne(
                    'SELECT count(*)
                                FROM CollectionVersionBlocks
                                INNER JOIN Pages
                                ON CollectionVersionBlocks.cID = Pages.cID
                                INNER JOIN CollectionVersions
                                ON CollectionVersions.cID = Pages.cID
                                WHERE CollectionVersions.cvIsApproved = 1
                                AND CollectionVersionBlocks.cvID = CollectionVersions.cvID
                                AND CollectionVersionBlocks.bID = ?',
                    array($block->bID));
                $url = $nh->getLinkToCollection($block->getBlockCollectionObject());
                ?>
                <tr>
                    <td><?= $text->entities($survey['surveyName']) ?></td>
                    <td><?= $text->entities($survey['answerSetCount']) ?></td>
                    <td style="min-width: 380px" class="text-right">
                        <form method="post" action="" style="display: inline">
                            <input type="hidden" name="qsID" value="<?= intval($qsid) ?>"/>
                            <input type="hidden" name="action" value="deleteFormAnswers"/>
                            <?php $valt->output('deleteFormAnswers') ?>
                            <div class="btn-group">
                                <a href="<?= URL::to($c->getCollectionPath() . '?qsid=' . $qsid) ?>"
                                   class="btn btn-default btn-sm">
                                    <?= t('View Responses') ?>
                                </a>
                                <a class="btn btn-default btn-sm" href="<?=$url?>">
                                    <?= t('Open Page') ?>
                                </a>
                                <button class="btn btn-danger btn-sm delete-form-answers"
                                        name='ccm-submit-button'>
                                    <?= t('Delete Submissions') ?>
                                </button>
                            </div>
                        </form>
                        <? if (!$in_use): { ?>
                            <form method="post" action="" style="display: inline">
                                <input type="hidden" name="bID" value="<?= intval($survey['bID']) ?>"/>
                                <input type="hidden" name="qsID" value="<?= intval($qsid) ?>"/>
                                <input type="hidden" name="action" value="deleteForm"/>
                                <?php $valt->output('deleteForm') ?>
                                <?= $ih->submit(t('Delete'), false, 'left', 'small error delete-form') ?>
                            </form>
                        <? }endif ?>
                    </td>
                </tr>
            <?php
            }
            ?>
            </tbody>
        </table>
    <?php
    }
    /*foreach ($surveys as $qsid => $survey): {
            $block = Block::getByID((int)$survey['bID']);
            if (!is_object($block)) {
                continue;
            }

            $url = $nh->getLinkToCollection($block->getBlockCollectionObject());
            ?>
            <tr>
                <td><?= $text->entities($survey['surveyName']) ?></td>
                <td><?= $text->entities($survey['answerSetCount']) ?></td>
                <td>
                    <?= $ih->button(
                        t('View Responses'),
                        DIR_REL . '/index.php?cID=' . $c->getCollectionID() . '&qsid=' . $qsid,
                        'left',
                        'small') ?>
                    <?= $ih->button(t('Open Page'), $url, 'left', 'small') ?>
                    <form method="post" action="" style="display: inline">
                        <input type="hidden" name="qsID" value="<?= intval($qsid) ?>"/>
                        <input type="hidden" name="action" value="deleteFormAnswers"/>
                        <?php $valt->output('deleteFormAnswers') ?>
                        <?= $ih->submit(
                            t('Delete Submissions'),
                            false,
                            'left',
                            'small error delete-form-answers') ?>
                    </form>
                    <? if (!$in_use): { ?>
                        <form method="post" action="" style="display: inline">
                            <input type="hidden" name="bID" value="<?= intval($survey['bID']) ?>"/>
                            <input type="hidden" name="qsID" value="<?= intval($qsid) ?>"/>
                            <input type="hidden" name="action" value="deleteForm"/>
                            <?php $valt->output('deleteForm') ?>
                            <?= $ih->submit(t('Delete'), false, 'left', 'small error delete-form') ?>
                        </form>
                    <? }endif ?>
                </td>
            </tr>
        <? }endforeach ?>
        </tbody>
        </table>
    <? } */
else {
        ?>
        <p><?= t('There are no available forms in your site.') ?></p>
    <? } ?>
    <?= $h->getDashboardPaneFooterWrapper(); ?>
<? } else: { ?>
    <?= $h->getDashboardPaneHeaderWrapper(
        t('Responses to %s', $surveys[$questionSet]['surveyName']),
        false,
        false,
        false); ?>
<div class="ccm-pane-body <? if (!$paginator || !strlen($paginator->getPages()) > 0) { ?> ccm-pane-body-footer <? } ?>">
    <? if (count($answerSets) == 0): { ?>
        <div><?= t('No one has yet submitted this form.') ?></div>
    <? } else: { ?>

        <div class="ccm-dashboard-header-buttons">
            <a id="ccm-export-results" class="btn btn-success" href="<?= $view->action('csv')?>?qsid=<?=$questionSet ?>">
                <i class='fa fa-download'></i> <?= t('Export to CSV') ?>
            </a>
        </div>

        <div class="form-results-container">
            <script>
                $(document).ready(function () {
                    if ($('.form-results-container')[0].scrollWidth > $('.ccm-pane-body').width()) {
                        $('#wide-content-notification').show();
                    }
                });
            </script>
            <p id="wide-content-notification"><?php echo t('* Scroll right to view full results'); ?></p>
            <table class="table table-striped">
                <thead>
                <tr>
                    <? if ($_REQUEST['sortBy'] == 'chrono') { ?>
                    <th class="header headerSortDown">
                        <a href="<?= $text->entities($urlhelper->unsetVariable('sortBy')) ?>">
                            <? } else { ?>
                            <th class="header headerSortUp">
                                <a href="<?= $text->entities($urlhelper->setVariable('sortBy', 'chrono')) ?>">
                                    <? } ?>
                                    <?= t('Date') ?>
                                </a>
                            </th>
                            <th><?= t('User') ?></th>
                            <? foreach ($questions as $question): { ?>
                                <th><?= $question['question'] ?></th>
                            <? }endforeach ?>
                            <th></th>
                </tr>
                </thead>
                <tbody>
                <? foreach ($answerSets as $answerSetId => $answerSet): { ?>
                    <tr>
                        <td><?= $dh->formatCustom($dh::DB_FORMAT, $answerSet['created']) ?></td>
                        <td><?
                            if ($answerSet['uID'] > 0) {
                                $ui = UserInfo::getByID($answerSet['uID']);
                                if (is_object($ui)) {
                                    print $ui->getUserName() . ' ';
                                }
                                print t('(User ID: %s)', $answerSet['uID']);
                            }
                            ?></td>
                        <?foreach ($questions as $questionId => $question): {
                            if ($question['inputType'] == 'fileupload') {
                                $fID = (int)$answerSet['answers'][$questionId]['answer'];
                                $file = File::getByID($fID);
                                if ($fID && $file) {
                                    $fileVersion = $file->getApprovedVersion();
                                    echo '<td><a href="' . $fileVersion->getRelativePath() . '">' .
                                        $text->entities($fileVersion->getFileName()) . '</a></td>';
                                } else {
                                    echo '<td>' . t('File not found') . '</td>';
                                }
                            } else {
                                if ($question['inputType'] == 'text') {
                                    echo '<td>' . $text->entities(
                                            $answerSet['answers'][$questionId]['answerLong']) . '</td>';
                                } else {
                                    echo '<td>' . $text->entities(
                                            $answerSet['answers'][$questionId]['answer']) . '</td>';
                                }
                            }
                        }

                        endforeach?>
                        <td>
                            <form method="post" action="" class='pull-right'>
                                <input type="hidden" name="qsid" value="<?= intval($answerSet['questionSetId']) ?>"/>
                                <input type="hidden" name="asid" value="<?= intval($answerSet['asID']) ?>"/>
                                <input type="hidden" name="action" value="deleteResponse"/>
                                <?php $valt->output('deleteResponse') ?>
                                <?= $ih->submit(t('Delete'), false, 'left', 'btn pull-right btn-danger delete-response') ?>
                            </form>
                        </td>
                    </tr>
                <? }endforeach ?>
                </tbody>
            </table>
        </div>
        </div>
        <? if ($paginator && strlen($paginator->getPages()) > 0) { ?>
            <div class="ccm-search-results-pagination">
                <ul class="pagination">
                    <li class="prev"><?= $paginator->getPrevious() ?></li>

                    <? // Call to pagination helper's 'getPages' method with new $wrapper var ?>
                    <?= $paginator->getPages('li') ?>

                    <li class="next"><?= $paginator->getNext() ?></li>
                </ul>
            </div>
        <? } ?>
    <? }endif ?>
    <?= $h->getDashboardPaneFooterWrapper(false); ?>
<? }endif ?>