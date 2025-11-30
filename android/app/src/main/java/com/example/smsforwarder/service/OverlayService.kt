package com.example.smsforwarder.service
import android.app.Service
import android.content.Intent
import android.os.IBinder
import android.view.*
import android.widget.*
import com.example.smsforwarder.util.ConfigStore
import com.example.smsforwarder.R
class OverlayService: Service(){
  private var wm:WindowManager?=null
  private var view:View?=null
  private lateinit var cfg:ConfigStore
  override fun onBind(intent: Intent?): IBinder?=null
  override fun onCreate(){
    cfg=ConfigStore(this)
    wm=getSystemService(WindowManager::class.java)
    val lp=WindowManager.LayoutParams(WindowManager.LayoutParams.WRAP_CONTENT,WindowManager.LayoutParams.WRAP_CONTENT,WindowManager.LayoutParams.TYPE_APPLICATION_OVERLAY,WindowManager.LayoutParams.FLAG_NOT_FOCUSABLE or WindowManager.LayoutParams.FLAG_LAYOUT_IN_SCREEN,PixelFormat.TRANSLUCENT)
    lp.x=cfg.getOverlayX(); lp.y=cfg.getOverlayY();
    val inflater=LayoutInflater.from(this)
    view=inflater.inflate(R.layout.overlay_view,null)
    val tv=view!!.findViewById<TextView>(R.id.tvConn)
    val list=view!!.findViewById<ListView>(R.id.list)
    val msgs=cfg.getMessages().map{ it.first+": "+it.second }
    list.adapter=ArrayAdapter(this,android.R.layout.simple_list_item_1,msgs)
    view!!.alpha=cfg.getOverlayAlpha()
    view!!.setOnTouchListener(object:View.OnTouchListener{
      private var lastX=0; private var lastY=0
      override fun onTouch(v:View, e:MotionEvent):Boolean{
        if(e.action==MotionEvent.ACTION_DOWN){ lastX=e.rawX.toInt(); lastY=e.rawY.toInt() }
        else if(e.action==MotionEvent.ACTION_MOVE){ val dx=e.rawX.toInt()-lastX; val dy=e.rawY.toInt()-lastY; lp.x+=dx; lp.y+=dy; wm?.updateViewLayout(view,lp); lastX=e.rawX.toInt(); lastY=e.rawY.toInt() }
        else if(e.action==MotionEvent.ACTION_UP){ cfg.setOverlayPos(lp.x,lp.y) }
        return true
      }
    })
    wm?.addView(view,lp)
    registerReceiver(object:android.content.BroadcastReceiver(){ override fun onReceive(c:android.content.Context?,i:Intent?){ val ok=i?.getBooleanExtra("ok",false)==true; tv.text=if(ok) "连接正常" else "连接异常" } }, android.content.IntentFilter("com.example.smsforwarder.STATUS"))
  }
  override fun onDestroy(){ if(view!=null){ wm?.removeView(view) } }
}
