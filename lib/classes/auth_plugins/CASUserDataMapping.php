<?
# Lifter007: TODO
# Lifter003: TODO
# Lifter010: TODO
/**
 * Interface for the user mapping used by StudIPAuthCAS
 */
interface CASUserDataMapping {

    // reads one attribute identified by a key of a given user
    function getUserData ($key, $username);
}
?>