package com.evoupsight.monitorPass.dataCollector.auth;

import com.evoupsight.monitorPass.dataCollector.auth.exception.InvalidProtocolException;

import java.io.UnsupportedEncodingException;
import java.security.InvalidKeyException;
import java.security.NoSuchAlgorithmException;
import java.security.SignatureException;
import java.security.spec.InvalidKeySpecException;
import java.util.regex.Matcher;
import java.util.regex.Pattern;


public class ScramSha1 {

    private static final Pattern
            CLIENT_FINAL_MESSAGE = Pattern.compile("(c=([^,]*),r=([^,]*)),p=(.*)$");

    private static final String CLIENT_HEADER = "biws";

    private static final String ClIENT_PASS = "pencil";

    private static final int PBKDF2Length = 20;

    /**
     * client username, stored after client first message came
     */
    private String cName;

    /**
     * client part nonce and server part nonce, stored after client first message came
     */
    private String mNonce;

    /**
     * client nonce, stored after client first message came
     */
    private String cNonce;

    /**
     * server nonce, generated after client first message came
     */
    private String sNonce;

    /**
     * salt string, generated after client first message came
     */
    private String salt;

    /**
     * iterations, generated after client first message came
     */
    private String iterations;

    /**
     * client first message bare, stored after client first message came
     */
    private String mClientFirstMessageBare;

    /**
     * server first message, stored before server first message send
     */
    private String mServerFirstMessage;

    /**
     *
     * @param clientFinalMessage client final message
     * @return server final message
     */
    public String prepareFinalMessage(String clientFinalMessage) throws InvalidProtocolException,
            InvalidKeySpecException, NoSuchAlgorithmException, UnsupportedEncodingException, SignatureException,
            InvalidKeyException {
        Matcher m = CLIENT_FINAL_MESSAGE.matcher(clientFinalMessage);
        if (!m.matches()) {
            throw new InvalidProtocolException();
        }
        String clientFinalMessageWithoutProof = m.group(1);
        String NonceFromClient = m.group(3);
        String proof = m.group(4);

        if (!mNonce.equals(NonceFromClient)) {
            throw new InvalidProtocolException();
        }
        int iter = Integer.parseInt(iterations);
        // 不能再计算一次authMessage，而应该用传过来的消息的几个字段client first message bare,server first message
        // 和client final message without proof
        // String authMessage = authMessage(cName, cNonce, sNonce, salt, CLIENT_HEADER, iter);
        String authMessage = mClientFirstMessageBare + "," + mServerFirstMessage + "," + clientFinalMessageWithoutProof;

        // todo 用pbkdf2生成好saltedPassword,并和“Server Key”字符串计算摘要
        byte[] bytes = PasswordHash.pbkdf2(ClIENT_PASS.toCharArray(), salt.getBytes(), iter, PBKDF2Length);
        String saltedPassword = new String(bytes, "UTF-8");
        String serverKey = HmacSha1Signature.calculateRFC2104HMAC(saltedPassword, "Server Key");
        return null;
    }

//    func clientFirstMessageBare(cName, cNonce []byte) (out []byte) {
//        out = []byte("n=")
//        out = append(out, cName...)
//        out = append(out, ",r="...)
//        out = append(out, cNonce...)
//        return
//    }

    public String clientFirstMessageBare(String cName, String cNonce) {
        return "n=" + cName + ",r=" + cNonce;
    }

//    func serverFirstMessage(sNonce, sSalt, cNonce, cName []byte, iterations int) (out []byte) {
//        nonce := append(cNonce, sNonce...)
//
//        out = append(out, "r="...)
//        out = append(out, nonce...)
//        out = append(out, ",s="...)
//        out = append(out, sSalt...)
//        out = append(out, ",i="...)
//        out = append(out, strconv.Itoa(iterations)...)
//
//        return
//    }

    public String serverFirstMessage(String sNonce, String salt, String cNonce, String cname, int iterations) {
        String nonce = cNonce + sNonce;
        return "r=" +
                nonce +
                ",s=" +
                salt +
                ",i=" +
                iterations;
    }

//    func clientFinalMessageWithoutProof(cHeader, cNonce, sNonce []byte) (out []byte) {
//        nonce := append(cNonce, sNonce...)
//
//        out = []byte("c=")
//        out = append(out, cHeader...)
//        out = append(out, ",r="...)
//        out = append(out, nonce...)
//        return
//    }

    public String clientFinalMessageWithoutProof(String cHeader, String cNonce, String sNonce) {
        String nonce = cNonce + sNonce;
        return "c=" + cHeader + ",r=" + nonce;
    }

//    func authMessage(cName, cNonce, sNonce, sSalt, cHeader []byte, iterations int) (out []byte) {
//        out = clientFirstMessageBare(cName, cNonce)
//        out = append(out, ","...)
//        out = append(out, serverFirstMessage(sNonce, sSalt, cNonce, cName, iterations)...)
//        out = append(out, ","...)
//        out = append(out, clientFinalMessageWithoutProof(cHeader, cNonce, sNonce)...)
//        return
//    }

    public String authMessage(String cName, String cNonce, String sNonce, String salt, String cHeader, int iterations) {
        return clientFirstMessageBare(cName, cNonce) +
                "," +
                serverFirstMessage(sNonce, salt, cNonce, cName, iterations) +
                "," +
                clientFinalMessageWithoutProof(cHeader, cNonce, sNonce);
    }


    public String getcName() {
        return cName;
    }

    public void setcName(String cName) {
        this.cName = cName;
    }

    public String getmNonce() {
        return mNonce;
    }

    public void setmNonce(String mNonce) {
        this.mNonce = mNonce;
    }

    public String getcNonce() {
        return cNonce;
    }

    public void setcNonce(String cNonce) {
        this.cNonce = cNonce;
    }

    public String getsNonce() {
        return sNonce;
    }

    public void setsNonce(String sNonce) {
        this.sNonce = sNonce;
    }

    public String getSalt() {
        return salt;
    }

    public void setSalt(String salt) {
        this.salt = salt;
    }

    public String getIterations() {
        return iterations;
    }

    public void setIterations(String iterations) {
        this.iterations = iterations;
    }

    public String getmClientFirstMessageBare() {
        return mClientFirstMessageBare;
    }

    public void setmClientFirstMessageBare(String mClientFirstMessageBare) {
        this.mClientFirstMessageBare = mClientFirstMessageBare;
    }

    public String getmServerFirstMessage() {
        return mServerFirstMessage;
    }

    public void setmServerFirstMessage(String mServerFirstMessage) {
        this.mServerFirstMessage = mServerFirstMessage;
    }
}
