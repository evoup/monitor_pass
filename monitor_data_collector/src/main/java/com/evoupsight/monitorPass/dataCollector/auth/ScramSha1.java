package com.evoupsight.monitorPass.dataCollector.auth;

import com.evoupsight.monitorPass.dataCollector.auth.exception.InvalidProtocolException;
import java.util.regex.Matcher;
import java.util.regex.Pattern;


public class ScramSha1 {

    private static final Pattern
            CLIENT_FINAL_MESSAGE = Pattern.compile("(c=([^,]*),r=([^,]*)),p=(.*)$");

    private static final String CLIENT_HEADER = "biws";

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
     *
     * @param clientFinalMessage client final message
     * @return server final message
     */
    public String prepareFinalMessage(String clientFinalMessage) throws InvalidProtocolException {
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
        String authMessage = authMessage(cName, cNonce, sNonce, salt, CLIENT_HEADER, Integer.parseInt(iterations));
        // todo 用pbkdf2生成好saltedPassword,并和“Server Key”字符串计算摘要
        //HmacSha1Signature.calculateRFC2104HMAC(authMessage, )
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
}
