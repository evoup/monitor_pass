import Vue from 'vue'
import Router from 'vue-router'

// in development-env not use lazy-loading, because lazy-loading too many pages will cause webpack hot update too slow. so only in production use lazy-loading;
// detail: https://panjiachen.github.io/vue-element-admin-site/#/lazy-loading

Vue.use(Router)

/* Layout */
import Layout from '../views/layout/Layout'

// noinspection NpmUsedModulesInstalled
/**
* hidden: true                   if `hidden:true` will not show in the sidebar(default is false)
* alwaysShow: true               if set true, will always show the root menu, whatever its child routes length
*                                if not set alwaysShow, only more than one route under the children
*                                it will becomes nested mode, otherwise not show the root menu
* redirect: noredirect           if `redirect:noredirect` will no redirect in the breadcrumb
* name:'router-name'             the name is used by <keep-alive> (must set!!!)
* meta : {
    title: 'title'               the name show in submenu and breadcrumb (recommend set)
    icon: 'svg-name'             the icon show in the sidebar
    breadcrumb: false            if false, the item will hidden in breadcrumb(default is true)
  }
**/
export const constantRouterMap = [
  { path: '/login', component: () => import('../views/login/index'), hidden: true },
  { path: '/404', component: () => import('../views/404'), hidden: true },

  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    name: 'Dashboard',
    hidden: true,
    children: [{
      path: 'dashboard',
      component: () => import('../views/dashboard/index')
    }]
  },

  {
    path: '/dashboard',
    component: Layout,
    redirect: null,
    name: 'Example',
    meta: { title: '概览', icon: 'dashboard2' },
    children: [
      {
        path: 'table',
        name: 'Table',
        component: () => import('../views/table/index'),
        meta: { title: '一览', icon: 'table' }
      },
      {
        path: 'tree',
        name: 'Tree',
        component: () => import('../views/tree/index'),
        meta: { title: '监控服务器状态', icon: 'tree' }
      }
    ]
  },
  {
    path: '/servers',
    component: Layout,
    redirect: null,
    name: 'Servers',
    meta: { title: '服务器', icon: 'server2' },
    children: [
      {
        path: '/add_server',
        name: 'Table1',
        component: () => import('../views/server/add_server'),
        meta: { title: '添加服务器', icon: 'form' }
      },
      {
        path: '/server_list',
        name: 'ServerList',
        component: () => import('../views/server/server_list'),
        meta: { title: '服务器详情管理', icon: 'server3' }
      },
      {
        path: 'batch_file_upload',
        name: 'Tree1',
        component: () => import('../views/tree/index'),
        meta: { title: '批量文件上传', icon: 'file_upload' }
      },
      {
        path: 'batch_command_execute',
        name: 'Tree2',
        component: () => import('../views/tree/index'),
        meta: { title: '批量命令执行', icon: 'command_exec' }
      }
    ]
  }, {
    path: '/config',
    component: Layout,
    redirect: null,
    name: 'Config',
    meta: { title: '配置', icon: 'system_config' },
    children: [
      {
        path: '/add_item',
        name: 'AddItem',
        component: () => import('../views/item/add_item'),
        meta: { title: '添加监控项', icon: 'template' },
        hidden: true
      },
      {
        path: '/change_item',
        name: 'ChangeItem',
        component: () => import('../views/item/change_item'),
        meta: { title: '修改监控项', icon: 'template' },
        hidden: true
      },
      {
        path: '/item_list',
        name: 'ItemList',
        component: () => import('../views/item/item_list'),
        meta: { title: '监控项', icon: 'template' },
        hidden: true
      },
      {
        path: '/trigger_list',
        name: 'TriggerList',
        component: () => import('../views/trigger/trigger_list'),
        meta: { title: '触发器列表', icon: 'trigger' },
        hidden: true
      },
      {
        path: '/add_trigger',
        name: 'AddTrigger',
        component: () => import('../views/trigger/create_trigger'),
        meta: { title: '创建触发器', icon: 'trigger' },
        hidden: true
      },
      {
        path: '/change_trigger',
        name: 'ChangeTrigger',
        component: () => import('../views/trigger/change_trigger'),
        meta: { title: '修改触发器', icon: 'trigger' },
        hidden: true
      },
      {
        path: '/template_list',
        name: 'TemplateList',
        component: () => import('../views/template/template_list'),
        meta: { title: '模板', icon: 'template' }
      },
      {
        path: '/add_template',
        name: 'AddTemplate',
        component: () => import('../views/template/add_template'),
        meta: { title: '添加模板', icon: 'template' },
        hidden: true
      },
      {
        path: '/change_template',
        name: 'ChangeTemplate',
        component: () => import('../views/template/change_template'),
        meta: { title: '修改模板', icon: 'template' },
        hidden: true
      },
      {
        path: '/general_conf',
        name: 'GeneralConf',
        component: () => import('../views/form/index'),
        meta: { title: '常规设置', icon: 'form' }
      },
      {
        path: '/server_group_list',
        name: 'ServerGroupManager',
        component: () => import('../views/server/server_group_list'),
        meta: { title: '服务器组管理', icon: 'server_group' }
      },
      {
        path: '/add_server_group',
        name: 'AddServerGroup',
        component: () => import('../views/server/add_server_group'),
        meta: { title: '添加服务器组', icon: 'form' },
        hidden: true
      },
      {
        path: '/add_user',
        name: 'AddUser',
        component: () => import('../views/user/add_user'),
        meta: { title: '添加用户', icon: 'form' },
        hidden: true
      },
      {
        path: '/change_user',
        name: 'ChangeUser',
        component: () => import('../views/user/change_user'),
        meta: { title: '更新用户', icon: 'form' },
        hidden: true
      },
      {
        path: '/user_list',
        name: 'UserManager',
        component: () => import('../views/user/user_list'),
        meta: { title: '用户管理', icon: 'user_manager' }
      },
      {
        path: '/user_group_list',
        name: 'UserGroupManager',
        component: () => import('../views/user/user_group_list'),
        meta: { title: '用户组管理', icon: 'user_group_manager' }
      },
      {
        path: '/add_user_group',
        name: 'AddUserGroup',
        component: () => import('../views/user/add_user_group'),
        meta: { title: '添加用户组', icon: 'form' },
        hidden: true
      },
      {
        path: '/idc_list',
        name: 'IDCManager',
        component: () => import('../views/idc/idc_list'),
        meta: { title: '机房管理', icon: 'jifang' }
      },
      {
        path: '/add_idc',
        name: 'AddIDC',
        component: () => import('../views/idc/add_idc'),
        meta: { title: '添加机房', icon: 'jifang' },
        hidden: true
      }
    ]
  },
  { path: '*', redirect: '/404', hidden: true }
]

export default new Router({
  // mode: 'history', //后端支持可开
  scrollBehavior: () => ({ y: 0 }),
  routes: constantRouterMap
})
