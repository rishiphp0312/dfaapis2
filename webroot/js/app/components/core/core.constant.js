angular.module(appConfig.appName)
.constant('AUTH_EVENTS', {
    loginSuccess: 'auth-login-success',
    loginFailed: 'auth-login-failed',
    logoutSuccess: 'auth-logout-success',
    sessionTimeout: 'auth-session-timeout',
    notAuthenticated: 'auth-not-authenticated',
    notAuthorized: 'auth-not-authorized',
    setCurrentUser: 'set-current-user'
})
.constant('SYS_EVENTS', {

})
.constant('USER_ROLES', {
    all: '*',
    superAdmin: 'SUPERADMIN',
    admin: 'ADMIN',
    templateUser: 'TEMPLATE',
    dataUser: 'DATAENTRY'
})
.constant('SERVICE_CALL', {
    system: {
        loginUser: 100,
        logoutUser: 103,
        getUserRolesList: 1108,
        getAllUsersList: 1202,
        getUserDbRoles: 1205,
        checkSessionDetails: 1206
    },
    database: {
        getDatabaseList: 1103,
        addNewDatabaseConnection: 1101,
        verifyConnectionName: 1102,
        deleteDatabaseConnection: 1104,
        testDatabaseConnection: 1105,
        getDatabaseDetails: 1106
    },
    userManagement: {
        addModifyUser: 1201,
        getUserList: 1109,
        getUserDetail: 111,
        deleteUsers: 1200,
        confirmPassword: 1204,
        resetPassword: 1203
    },
    templateManagement: {
        getIndicatorList: 113,
        getIndicatorDetails: 114,
        importFile: 2401,
        exportFile: 905,
        getIUSDetails: 2211,
        saveIUSValidations: 2212
    },
    dataEntryManagement: {
        getData: 2403,
        saveData: 2404
    },
    commonService: {
        getIUSList: 2209,
        getAreaList: 2209,
        getTPList: 2405,
        getSourceList: 2405,
        getICList: 2209,
        getIndicatorList : 2209
    }
})
.constant('ERROR_CODE', {
    'DFAERR100': '',
    'DFAERR101': '',
    'DFAERR102': '',
    'DFAERR103': '',
    'DFAERR104': 'Cannot save the password as the activation link has expired.',
    'DFAERR105': '',
    'DFAERR106': '',
    'DFAERR107': '',
    'DFAERR108': '',
    'DFAERR109': '',
    'DFAERR110': '',
    'DFAERR111': '',
    'DFAERR112': '',
    'DFAERR113': '',
    'DFAERR114': '',
    'DFAERR115': 'Cannot save the password as the activation link is invalid.',
    'DFAERR116': '',
    'DFAERR117': 'Cannot save the password as the activation link is invalid.',
    'DFAERR118': 'Cannot Add/Modify user as the email entered already exists.',
    'DFAERR119': 'Cannot Add/Modify user as user already exists for this database.'
})
.constant('MENU_SECTIONS', {
    template: 'template',
    data: 'data',
    databaseSettings: 'databaseSettings',
    publishData: 'publishData'
})
.constant('MENU_SECTIONS_ACCESS', {
    template: ['SUPERADMIN', 'ADMIN', 'TEMPLATE'],
    data: ['SUPERADMIN', 'ADMIN', 'DATAENTRY'],
    databaseSettings: ['SUPERADMIN', 'ADMIN'],
    publishData: ['SUPERADMIN', 'ADMIN']
})