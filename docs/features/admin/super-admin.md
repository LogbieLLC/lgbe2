# Super Admin Feature

The LGBE2 platform includes a super admin feature that allows designated users to have special privileges that are protected from regular user interfaces.

## Characteristics

- Super admin users can only be created through artisan commands
- Super admin users can only be deleted through artisan commands
- Super admin status cannot be modified through regular web interfaces

## Commands

### Create a Super Admin

To create a new user as a super admin:

```bash
php artisan make:super-admin --create --name="Admin Name" --username="admin" --email="admin@example.com" --password="secure_password"
```

You can also run the command without parameters to be prompted for the information:

```bash
php artisan make:super-admin --create
```

### Promote an Existing User to Super Admin

To promote an existing user to super admin status:

```bash
php artisan make:super-admin --email="user@example.com"
```

### Delete a Super Admin

To delete a super admin user:

```bash
php artisan delete:super-admin user@example.com
```

## Security Considerations

- Super admin status is protected by middleware in the application
- Regular deletion methods have checks to prevent super admin deletion
- Only users with command-line access to the server can manage super admins
