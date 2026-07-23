<a href="#">
  <strong id="following" class="stat">
    {{ count($user->followings) }}
  </strong>
  关注
</a>
<a href="#">
  <strong id="followers" class="stst">
    {{ count($user->followers) }}
  </strong>
  粉丝
</a>
<a href="#">
  <strong id="statuses" class="stst">
    {{ $user->statuses()->count() }}
  </strong>
  微博
</a>
