<?php

/**
 * Matchs
 *
 * This class has been auto-generated by the Doctrine ORM Framework
 *
 * @package    PhpProject1
 * @subpackage model
 * @author     Your name here
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
class Matchs extends BaseMatchs {

    const STATUS_NOT_STARTED = 0;
    const STATUS_STARTING = 1;
    const STATUS_WU_KNIFE = 2;
    const STATUS_KNIFE = 3;
    const STATUS_END_KNIFE = 4;
    const STATUS_WU_1_SIDE = 5;
    const STATUS_FIRST_SIDE = 6;
    const STATUS_WU_2_SIDE = 7;
    const STATUS_SECOND_SIDE = 8;
    const STATUS_WU_OT_1_SIDE = 9;
    const STATUS_OT_FIRST_SIDE = 10;
    const STATUS_WU_OT_2_SIDE = 11;
    const STATUS_OT_SECOND_SIDE = 12;
    const STATUS_END_MATCH = 13;
    const STATUS_ARCHIVE = 14;

    public function getNbRound() {
        return $this->score_a + $this->score_b + 1;
    }

    public function getStatusText() {
        switch ($this->getStatus()) {
            case self::STATUS_NOT_STARTED:
                return "Not started";
            case self::STATUS_STARTING:
                return "Starting";
            case self::STATUS_WU_KNIFE:
                return "Warmup Knife";
            case self::STATUS_KNIFE:
                return "Knife Round";
            case self::STATUS_END_KNIFE:
                return "Waiting choose team";
            case self::STATUS_WU_1_SIDE:
                return "Warmup first side";
            case self::STATUS_FIRST_SIDE:
                return "First side - #" . $this->getNbRound();
            case self::STATUS_WU_2_SIDE:
                return "Warmup second side";
            case self::STATUS_SECOND_SIDE:
                return "Second side - #" . $this->getNbRound();
            case self::STATUS_WU_OT_1_SIDE:
                return "Warmup first side OT";
            case self::STATUS_OT_FIRST_SIDE:
                return "First side OT - #" . $this->getNbRound();
            case self::STATUS_WU_OT_2_SIDE:
                return "Warmup second side OT";
            case self::STATUS_OT_SECOND_SIDE:
                return "Second side OT - #" . $this->getNbRound();
            case self::STATUS_END_MATCH:
                return "Finished";
            case self::STATUS_ARCHIVE:
                return "Archived";
        }
    }

    public function getRoundSummaries() {
        return RoundSummaryTable::getInstance()->createQuery()->where("match_id = ?", $this->getId())->andWhere("map_id = ?", $this->getMap()->getId())->orderBy("round_id ASC")->execute();
    }

    public function getCurrentPlayers() {
        return PlayersTable::getInstance()
                        ->createQuery()
                        ->where("match_id = ?", $this->getId())
                        ->andWhere("map_id = ?", $this->getMap()->getId())
                        ->andWhere("team IN ?", array(array("a", "b")))
                        ->orderBy("pseudo ASC")
                        ->execute();
    }

    public function getLastRound() {
        return RoundSummaryTable::getInstance()
                        ->createQuery()
                        ->where("match_id = ?", $this->getId())->andWhere("map_id = ?", $this->getMap()->getId())
                        ->orderBy("round_id DESC")
                        ->limit(3)
                        ->execute();
    }

    public function getActionAdmin($enable) {
        if (!$enable) {
            if ($this->getStatus() == self::STATUS_NOT_STARTED) {
                return array(
                    array("label" => "Start", "route" => "matchs_start", "add_class" => "btn-success", "type" => "routing"),
                    array("label" => "Edit", "route" => "matchs_edit", "add_class" => "btn-primary", "type" => "routing"),
                    array("label" => "Delete", "route" => "matchs_delete", "add_class" => "btn-danger", "type" => "routing")
                );
            }

            if ($this->getStatus() == self::STATUS_END_MATCH) {
                return array(
                    array("label" => "Archive", "route" => "match_put_archive", "add_class" => "btn-info", "type" => "routing"),
                    array("label" => "Delete", "route" => "matchs_delete", "add_class" => "btn-danger", "type" => "routing")
                );
            }

            if ($this->getEnable() == 0) {
                $tab = array();
                if ($this->getServer()->exists()) {
                    $tab[] = array("label" => "Restart", "route" => "matchs_start_retry", "add_class" => "btn-success", "type" => "routing");
                } else {
                    $tab[] = array("label" => "Restart on another Server", "route" => "matchs_start", "add_class" => "btn-success", "type" => "routing");
                }

                $tab[] = array("label" => "Reset", "route" => "matchs_reset", "add_class" => "btn-warning", "type" => "routing");
                $tab[] = array("label" => "Edit", "route" => "matchs_edit", "add_class" => "btn-primary", "type" => "routing");
                $tab[] = array("label" => "Delete", "route" => "matchs_delete", "add_class" => "btn-danger", "type" => "routing");

                return $tab;
            }
        } else {
            if ($this->getStatus() == self::STATUS_END_MATCH) {
                return array(
                    array("label" => "Archive", "route" => "match_put_archive", "add_class" => "btn-info", "type" => "routing"),
                    array("label" => "Delete", "route" => "matchs_delete", "add_class" => "btn-danger", "type" => "routing")
                );
            }

            //if (($this->getStatus() >= Matchs::STATUS_WU_KNIFE) && ($this->getStatus() <= Matchs::STATUS_WU_OT_2_SIDE)) {
            $actions[] = array("label" => "Stop", "add_class" => "btn-danger", "action" => "stopNoRs", "type" => "running", "style" => "display:inline;");
            $actions[] = array("label" => "Stop with Restart", "add_class" => "btn-danger", "action" => "stop", "type" => "running", "style" => "display:inline;");
            $actions[] = array("label" => "Streamer Ready", "action" => "streamerready", "add_class" => "btn-primary", "type" => "running", "style" => "display:inline;");
            $actions[] = array("label" => "Fix Sides", "action" => "fixsides", "type" => "running", "add_class" => "btn-primary", "style" => "display:inline;");
            //if ($this->getStatus() == Matchs::STATUS_WU_KNIFE) {
            $actions[] = array("label" => "Skip Knife", "action" => "passknife", "type" => "warmupknife", "style" => "display:none;");
            $actions[] = array("label" => "Force Knife", "action" => "forceknife", "type" => "warmupknife", "style" => "display:none;");
            //if ($this->getStatus() == Matchs::STATUS_END_KNIFE) {
            $actions[] = array("label" => "End Knife", "action" => "forceknifeend", "type" => "endknife", "style" => "display:none;");
            //if (in_array($this->getStatus(), array(Matchs::STATUS_WU_1_SIDE, Matchs::STATUS_WU_2_SIDE, Matchs::STATUS_WU_OT_1_SIDE, Matchs::STATUS_WU_OT_2_SIDE))) {
            $actions[] = array("label" => "Skip Warmup", "action" => "forcestart", "type" => "skipwarmup", "style" => "display:none;");
            //if (in_array($this->getStatus(), array(Matchs::STATUS_KNIFE, Matchs::STATUS_FIRST_SIDE, Matchs::STATUS_SECOND_SIDE, Matchs::STATUS_OT_FIRST_SIDE, Matchs::STATUS_OT_SECOND_SIDE))) {
            $actions[] = array("label" => "Stop to warmup", "action" => "stopback", "type" => "endmatch", "style" => "display:none;");
            $actions[] = array("label" => "Un/Pause", "action" => "pauseunpause", "type" => "endmatch", "style" => "display:none;");
        }
        $actions[] = array("label" => "RCON", "route" => "matchs_rcon", "type" => "routing");
        return $actions;
    }

}