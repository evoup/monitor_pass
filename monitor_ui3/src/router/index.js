import Vue from 'vue'
import Router from 'vue-router'

// in development-env not use lazy-loading, because lazy-loading too many pages will cause webpack hot update too slow. so only in production use lazy-loading;
// detail: https://panjiachen.github.io/vue-element-admin-site/#/lazy-loading

Vue.use(Router)

/* Layout */
import Layout from '../views/layout/Layout'

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
  { path: '/login', component: () => import('@/views/login/index'), hidden: true },
  { path: '/404', component: () => import('@/views/404'), hidden: true },

  {
    path: '/',
    component: Layout,
    redirect: '/dashboard',
    name: 'Dashboard',
    hidden: true,
    children: [{
      path: 'dashboard',
      component: () => import('@/views/dashboard/index')
    }]
  },

  {
    path: '/dashboard',
    component: Layout,
    redirect: '/example/table',
    name: 'Example',
    meta: { title: '概览', icon: 'example' },
    children: [
      {
        path: 'table',
        name: 'Table',
        component: () => import('@/views/table/index'),
        meta: { title: '一览', icon: 'table' }
      },
      {
        path: 'tree',
        name: 'Tree',
        component: () => import('@/views/tree/index'),
        meta: { title: '监控服务器状态', icon: 'tree' }
      }
    ]
  },
  {
    path: '/servers',
    component: Layout,
    redirect: '/example/table',
    name: 'Servers',
    meta: { title: '服务器', icon: 'example' },
    children: [
      {
        path: '/add_server',
        name: 'Table1',
        component: () => import('@/views/server/add_server'),
        meta: { title: '添加服务器', icon: 'form' }
      },
      {
        path: '/server_list',
        name: 'Table2',
        component: () => import('@/views/server/server_list'),
        meta: { title: '服务器详情管理', icon: 'form' }
      },
      {
        path: 'batch_file_upload',
        name: 'Tree1',
        component: () => import('@/views/tree/index'),
        meta: { title: '批量文件上传', icon: 'tree' }
      },
      {
        path: 'batch_command_execute',
        name: 'Tree2',
        component: () => import('@/views/tree/index'),
        meta: { title: '批量命令执行', icon: 'tree' }
      }
    ]
  }, {
    path: '/config',
    component: Layout,
    redirect: '/example/table',
    name: 'Config',
    meta: { title: '配置', icon: 'example' },
    children: [
      {
        path: '/general_conf',
        name: 'GeneralConf',
        component: () => import('@/views/form/index'),
        meta: { title: '常规设置', icon: 'form' }
      },
      {
        path: '/server_group_list',
        name: 'ServerGroupManager',
        component: () => import('@/views/server/server_group_list'),
        meta: { title: '服务器组管理', icon: 'form' }
      },
      {
        path: '/add_server_group',
        name: 'AddServerGroup',
        component: () => import('@/views/server/add_server_group'),
        meta: { title: '添加服务器组', icon: 'form' },
        hidden: true
      },
      {
        path: '/user_list',
        name: 'UserManager',
        component: () => import('@/views/user/user_list'),
        meta: { title: '用户管理', icon: 'form' }
      },
      {
        path: '/user_group_list',
        name: 'UserGroupManager',
        component: () => import('@/views/user/user_group_list'),
        meta: { title: '用户组管理', icon: 'form' }
      },
      {
        path: '/add_user_group',
        name: 'AddUserGroup',
        component: () => import('@/views/user/add_user_group'),
        meta: { title: '添加用户组', icon: 'form' },
        hidden: true
      }
    ]
  }, {
    path: '/form',
    component: Layout,
    children: [
      {
        path: 'index',
        name: 'Form',
        component: () => import('@/views/form/index'),
        meta: { title: 'Form', icon: 'form' }
      }
    ]
  },

  {
    path: '/nested',
    component: Layout,
    redirect: '/nested/menu1',
    name: 'Nested',
    meta: {
      title: 'Nested',
      icon: 'nested'
    },
    children: [
      {
        path: 'menu1',
        component: () => import('@/views/nested/menu1/index'), // Parent router-view
        name: 'Menu1',
        meta: { title: 'Menu1' },
        children: [
          {
            path: 'menu1-1',
            component: () => import('@/views/nested/menu1/menu1-1'),
            name: 'Menu1-1',
            meta: { title: 'Menu1-1' }
          },
          {
            path: 'menu1-2',
            component: () => import('@/views/nested/menu1/menu1-2'),
            name: 'Menu1-2',
            meta: { title: 'Menu1-2' },
            children: [
              {
                path: 'menu1-2-1',
                component: () => import('@/views/nested/menu1/menu1-2/menu1-2-1'),
                name: 'Menu1-2-1',
                meta: { title: 'Menu1-2-1' }
              },
              {
                path: 'menu1-2-2',
                component: () => import('@/views/nested/menu1/menu1-2/menu1-2-2'),
                name: 'Menu1-2-2',
                meta: { title: 'Menu1-2-2' }
              }
            ]
          },
          {
            path: 'menu1-3',
            component: () => import('@/views/nested/menu1/menu1-3'),
            name: 'Menu1-3',
            meta: { title: 'Menu1-3' }
          }
        ]
      },
      {
        path: 'menu2',
        component: () => import('@/views/nested/menu2/index'),
        meta: { title: 'menu2' }
      }
    ]
  },

  {
    path: 'external-link',
    component: Layout,
    children: [
      {
        path: 'https://panjiachen.github.io/vue-element-admin-site/#/',
        meta: { title: 'External Link', icon: 'link' }
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
