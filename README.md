# Secret Santa Bot

This is a tool to facilitate Secret Santa sorting and recordkeeping across years.

Here are the core features:

 * Keep track of historic SS data
 * Allow people to sign up for SS
 * Match people based on criteria
 * Provide a way for people to update their SS status
 * Admins should be able to manage SS

Here's some table definitions:

*users*:
```user_id // user_name // status // type ```

```status``` in this table refers to ban/auth status.

*pairings*:
``` pairing_id // year / type / santa / santee / status ```

```status``` in this table refers to completion status (incomplete, sent, received, etc).

