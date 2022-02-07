# hyperf-cms

<p>
<img src="https://hyperf-cms.oss-cn-guangzhou.aliyuncs.com/logo/logo_color.png" />
</p>

<p>
  <img alt="Version" src="https://img.shields.io/badge/version-1.2-blue.svg?cacheSeconds=2592000" />
  <img src="https://img.shields.io/badge/node-%3E%3D%20 12.16.1-blue.svg" />
  <img src="https://img.shields.io/badge/npm-%3E%3D%206.13.4-blue.svg" />
  <img src="https://img.shields.io/badge/php-%3E%3D7.3.0-red" />
  <a href="https://github.com/Nirongxu/vue-xuAdmin/blob/master/README.md">
    <img alt="Documentation" src="https://img.shields.io/badge/documentation-yes-brightgreen.svg" target="_blank" />
  </a>
  <a href="https://github.com/Nirongxu/vue-xuAdmin/blob/master/LICENSE">
    <img alt="License: MIT" src="https://img.shields.io/badge/License-MIT-yellow.svg" target="_blank" />
  </a>
</p>

> 基于 hyperf + vue + element 开发的 RBAC 权限管理，后台模板

#### 🏠 [项目体验](http://cms.linyiyuan.top/)

> 账号密码通过注册获取, 默认注册账号仅拥有部分权限

## Author

👤 **YiYuanlin**

- Github: [@linyiyuan~](https://github.com/linyiyuan)
- WeiXin: 13211035441
- email: linyiyuann@163.com

## Prerequisites

- node >= 12.16.1
- npm >= 6.13.4
- php >= 7.3.0
- swoole >= 4.5.3
- hyperf >= 2.1
- vue >= 2.0
- element >= 2.15.3

## 项目源码

码云作为国内同步仓库，可解决 Github 克隆缓慢的问题，两个平台的代码都是同步更新的，按需选择

|        | 后端源码                                                                             | 前端源码                                                                             |
| ------ | ------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------ |
| Github | [https://github.com/hyperf-cms/hyperf-api](https://github.com/hyperf-cms/hyperf-api) | [https://github.com/hyperf-cms/hyperf-cms](https://github.com/hyperf-cms/hyperf-cms) |
| Gitee  | [https://gitee.com/hyperf-cms/hyperf-api](https://gitee.com/hyperf-cms/hyperf-api)   | [https://gitee.com/hyperf-cms/hyperf-cms](https://gitee.com/hyperf-cms/hyperf-cms)   |

## 更新日志 （显示最新版本更新日志）

# V1.3 版本更新

## 优化

1. 升级 Element 版本为 2.15.3
2. 升级 Hyperf 为最新 2.1 版本
3. 升级 webpack 打包，优化打包方式，使打包编译速度更快
4. 优化数据库表迁移文件，并增加字典，权限初始化数据操作
5. 去除一些垃圾冗余文件
6. 优化了 scss 样式问题，解决掉一些样式混乱问题
7. 优化了前端导航栏的样式，增加面包屑组件，将原有顶部导航封装成组件，通过开关控制彼此
8. 优化权限模块，增加目录菜单类型，重新生成权限初始化文件
9. 优化聊天页面样式
10. 优化登陆/注册页面的验证码，出现错误重新生成验证码
11. 优化聊天系统断线重连会反复通知用户问题，增加是否重连参数
12. 修复聊天系统中图片放大失效问题
13. 修复三级以及超过三级以上菜单路由失效问题
14. 修复群聊中用户退群后聊天记录未销毁 bug
15. 修复项目初始化操作时各种 bug 的出现
16. 修复系统日志组件路径错误问题，重新生成权限初始化列表

## 新增

1. 新增登陆页面注册入口
2. 新增全局参数模块
3. 新增登陆页面图标
4. 新增加了一些图标，并增加图标选择器组件
5. 在技术支持模块下增加 json 解析模块
6. 增加页面布局设置， 里面可以控制顶部导航，标签页，Logo 显示，动态标题，以及后台提示灯按钮开关
7. 顶部新增 Github,文档 入口按钮
8. 增加项目系统控制模块，通过该模块可以控制后台一些配置开关
9. 新增系统维护中间件
10. 新增全局按钮组，增加表格字段选择，搜索框隐藏，复制 Excel，导出 Excel 等公共按钮组件
11. 在所有模块中增加应用全局按钮组组件
12. 聊天模块新增加群抽屉，取消原先右侧导航
13. 聊天模块增加消息合并转发，逐条转发，批量删除功能
14. 聊天模块增加视频消息类型，支持在线播放视频

## 结语

如果这个框架对你有帮助的话，请不要吝啬你的 star

## 捐赠

> 捐杯咖啡或者一瓶肥宅快乐水

<table>
    <tr>
        <td ><img style="display: inline-block;width: 300px;height: 300px" src="https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20210527115716.png" ><p style="text-align: center">支付宝</p></td>
        <td ><img style="display: inline-block;width: 300px;height: 300px" src="https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/%E5%BE%AE%E4%BF%A1%E5%9B%BE%E7%89%87_20210527120018.png" ><p style="text-align: center">微信</p></td>
    </tr>
</table>

## 项目展示

![登陆页](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/1.png)
![首页](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/2.png)
![导航页](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/3.png)
![权限管理](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/4.png)
![添加权限](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/5.png)
![聊天模块](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/6.png)
![群聊](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/7.png)
![邀请组员](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/8.png)
![聊天设置](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/9.png)
![好友列表](https://shmily-album.oss-cn-shenzhen.aliyuncs.com/photo_album_17/10.png)

## 📝 License

Copyright © 2021 [linyiyuan](https://github.com/linyiyuan).<br />
This project is [MIT](https://github.com/hyperf-cms/hyperf-api/master/LICENSE) licensed.

---

_This README was generated with ❤️ by [readme-md-generator](https://github.com/kefranabg/readme-md-generator)_
