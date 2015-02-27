<?php

session_start();

include 'user_func.php';

if (isset($_POST['good'])) {
  $id = $_POST['id'];
  add_like($id, 'up');
  header("Location: index.php?id=$id");
  exit();
}

if (isset($_POST['bad'])) {
  $id = $_POST['id'];
  article_add_like($id, 'down');
  header("Location: index.php?id=$id");
  exit();
}

if (isset($_POST['bad'])) {
  $id = $_POST['id'];
  article_add_like($id, 'down');
  header("Location: index.php?id=$id");
  exit();
}

if (isset($_POST['rating'])) {
  article_add_rating($_POST);
  header("Location: index.php?id=$id");
  exit();
}

if (isset($_POST['delete'])) {
  article_delete_rating($_POST);
  header("Location: index.php?id=$id");
  exit();
}