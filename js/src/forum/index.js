import { extend } from 'flarum/extend';
import app from 'flarum/app';
import LogInButtons from 'flarum/components/LogInButtons';
import LogInButton from 'flarum/components/LogInButton';

app.initializers.add('flarum-microsoft-auth', () => {
  extend(LogInButtons.prototype, 'items', function(items) {
    items.add('microsoft',
      <LogInButton
        className="Button LogInButton--microsoft"
        icon="fab fa-microsoft"
        path="/auth/microsoft">
        {app.translator.trans('flarum-microsoft-auth.forum.log_in.with_microsoft_button')}
      </LogInButton>
    );
  });
});
